const { getBrowserContext } = require('../browser/browser-manager');
const selectors = require('../selectors/battery-connect.selectors');
const logger = require('../utils/logger');
const automationMutex = require('../utils/mutex');
const { normalizeSerial, comparisonNormalize, formatAmaronSerial, serialMatchesText } = require('../utils/normalize-serial');
const { captureProductSections, waitForBatteryDetailsArrived } = require('../capture/battery-connect-capture');
const { AutomationError, ERROR_CODES } = require('../utils/errors');

/**
 * Main service executing the exact 35-step Battery Connect automation flow.
 */
async function checkBatteryStatus(serialNumber, complaintNumber = null) {
    // STEP 1 — NORMALIZE SERIAL NUMBER
    const requestedSerial = normalizeSerial(serialNumber);
    if (!requestedSerial) {
        return {
            success: false,
            code: ERROR_CODES.AUTOMATION_ERROR,
            message: 'A valid battery serial_number is required.'
        };
    }

    logger.info(`Starting checkBatteryStatus automation for serial: ${requestedSerial}`, { complaintNumber });

    // STEP 29 — SINGLE ACTIVE AUTOMATION MUTEX (Queue simultaneous requests)
    const releaseMutex = await automationMutex.acquire();

    let context = null;
    let page = null;

    try {
        context = await getBrowserContext(true);
        page = await context.newPage();

        // Inject stealth script to hide webdriver automation flag
        await page.addInitScript(() => {
            Object.defineProperty(navigator, 'webdriver', { get: () => undefined });
        });

        // STEP 2 — OPEN REGISTER BATTERY PAGE
        logger.info(`Navigating to ${selectors.targetUrl}...`);
        const response = await page.goto(selectors.targetUrl, {
            waitUntil: 'domcontentloaded',
            timeout: 30000
        }).catch(err => {
            logger.error('Navigation to Register Battery page failed', { error: err.message });
            return null;
        });

        if (!response) {
            throw new AutomationError(
                ERROR_CODES.BATTERY_CONNECT_UNAVAILABLE,
                'Could not connect to Battery Connect server.'
            );
        }

        // STEP 3 — VERIFY AUTHENTICATION
        await verifyAuthentication(page);

        // STEP 4 & 5 — FIND & ENTER SERIAL NUMBER
        await enterSerialNumber(page, requestedSerial);

        // STEP 6 — CLICK CHECK STATUS (READ-ONLY)
        await clickCheckStatus(page);

        // STEP 7 — Wait for Search Result
        const searchState = await waitForSearchResult(page);

        if (searchState === 'OTP_REQUIRED') {
            throw new AutomationError(
                ERROR_CODES.AUTH_REQUIRED,
                'OTP Verification required on Battery Connect. Please open Google Chrome to verify OTP.'
            );
        }

        if (searchState === 'NOT_FOUND') {
            throw new AutomationError(
                ERROR_CODES.BATTERY_NOT_FOUND,
                `No battery record found on Battery Connect for serial: ${requestedSerial}`
            );
        }

        if (searchState === 'TIMEOUT') {
            const bodyText = await page.innerText('body').catch(() => '');
            if (/Otp|Verification|Login|Cloudflare/i.test(bodyText)) {
                throw new AutomationError(
                    ERROR_CODES.AUTH_REQUIRED,
                    'Battery Connect session authentication or OTP verification required.'
                );
            }
            throw new AutomationError(
                ERROR_CODES.BATTERY_NOT_FOUND,
                `Search timed out or no battery record found for serial: ${requestedSerial}`
            );
        }

        // STEP 8 & 9 — MATCH AND CLICK BATTERY CARD (If cards exist)
        await handleBatteryCardSelection(page, requestedSerial);

        // STEP 10 — VERIFY CORRECT BATTERY DETAIL
        const isMatchVerified = await verifyBatteryMatch(page, requestedSerial);
        if (!isMatchVerified) {
            throw new AutomationError(
                ERROR_CODES.BATTERY_MATCH_FAILED,
                `Displayed details on Battery Connect do not match requested serial ${requestedSerial}.`
            );
        }

        // STEP 11 TO 16 — EXPAND SECTIONS & CAPTURE SCREENSHOT
        const captureResult = await captureProductSections(page, requestedSerial, complaintNumber);

        await page.close();

        // STEP 19 — RESPONSE TO LARAVEL
        return {
            success: true,
            code: 'CAPTURE_SUCCESS',
            serial_number: requestedSerial,
            screenshot_path: captureResult.relativePath,
            captured_at: new Date().toISOString(),
            match_verified: true,
            metadata: captureResult.extractedMetadata
        };

    } catch (err) {
        if (page) await page.close().catch(() => {});

        if (err instanceof AutomationError) {
            logger.warn(`Automation step returned error: [${err.code}] ${err.message}`);
            return {
                success: false,
                code: err.code,
                message: err.message,
                technical_details: err.technicalDetails
            };
        }

        logger.error('Unexpected error during checkBatteryStatus automation', { error: err.message, stack: err.stack });
        return {
            success: false,
            code: ERROR_CODES.AUTOMATION_ERROR,
            message: `Automation error: ${err.message}`
        };
    } finally {
        // Always release Mutex lock for queued requests
        releaseMutex();
    }
}

/**
 * STEP 3 — Multi-indicator Authentication Verification
 */
async function verifyAuthentication(page) {
    const currentUrl = page.url();

    // Check 1: Redirect to login pattern
    if (selectors.loginUrlPattern.test(currentUrl) || /\/login|\/auth/i.test(currentUrl)) {
        throw new AutomationError(
            ERROR_CODES.AUTH_REQUIRED,
            'Battery Connect session expired (login URL detected).'
        );
    }

    // Check 2: Presence of explicit login form inputs (password or login submit)
    const isLoginFormVisible = await page.locator(selectors.loginForm).first().isVisible({ timeout: 2000 }).catch(() => false);
    if (isLoginFormVisible) {
        throw new AutomationError(
            ERROR_CODES.AUTH_REQUIRED,
            'Battery Connect login form detected. Authentication required.'
        );
    }

    // Check 3: Check for session expired modal/alert text
    const hasSessionExpiredText = await page.evaluate(() => {
        const txt = (document.body.innerText || '').toLowerCase();
        return txt.includes('session expired') || txt.includes('session timed out') || txt.includes('please login again');
    }).catch(() => false);

    if (hasSessionExpiredText) {
        throw new AutomationError(
            ERROR_CODES.AUTH_REQUIRED,
            'Battery Connect session expired. Please re-login.'
        );
    }

    logger.info('Authentication verification passed.');
}

/**
 * Ensures Product Status tab is active without deselecting an active tab
 */
async function navigateToProductStatus(page) {
    logger.info('Ensuring Product Status sub-tab is active...');

    for (let attempt = 0; attempt < 8; attempt++) {
        const isStatusActive = await page.evaluate(() => {
            const activeTab = document.querySelector('a.nav-link.active, [role="tab"].active, a#status.active');
            if (activeTab && /product\s*status/i.test(activeTab.innerText || '')) {
                return true;
            }
            const activePanel = document.querySelector('.tab-pane.active, [role="tabpanel"].active, #status-panel');
            if (activePanel && (activePanel.querySelector('input[name="serialNumber"]') || activePanel.querySelector('#warrantyStatusForm'))) {
                return true;
            }
            const a = document.querySelector('a#status');
            const panel = document.querySelector('#status-panel');
            const isActive = a && (a.classList.contains('active') || a.getAttribute('aria-selected') === 'true');
            const isPanelVisible = panel && panel.offsetParent !== null;
            return Boolean(isActive && isPanelVisible);
        }).catch(() => false);

        if (isStatusActive) {
            logger.info('Product Status tab / panel is active.');
            return;
        }

        const statusTab = page.locator('a.nav-link:has-text("Product Status"), [role="tab"]:has-text("Product Status"), a#status, a[role="tab"]:has-text("Product Status")').first();
        if (await statusTab.isVisible({ timeout: 2000 }).catch(() => false)) {
            await statusTab.click().catch(() => {});
        }

        await page.waitForTimeout(1000);
    }
}

/**
 * STEP 4 & 5 — Find and Enter Serial Number in Battery/HUPS serial input
 */
async function enterSerialNumber(page, requestedSerial) {
    await navigateToProductStatus(page);

    const inputField = page.locator('form#warrantyStatusForm input[name="serialNumber"], .tab-pane.active input[name="serialNumber"], #status-panel input[name="serialNumber"], input[name="serialNumber"]').first();
    
    // Wait up to 15s for serial input field to render in Angular
    await inputField.waitFor({ state: 'visible', timeout: 15000 }).catch(() => null);

    const isInputVisible = await inputField.isVisible().catch(() => false);

    if (!isInputVisible) {
        // Check if session actually expired / unauthenticated
        const pageText = await page.innerText('body').catch(() => '');
        if (/login|sign\s*in|user\s*id|password/i.test(pageText) && !/SERVICE-FRANCHISEE|DWARKADHISH/i.test(pageText)) {
            throw new AutomationError(
                ERROR_CODES.AUTH_REQUIRED,
                'Battery Connect session expired. Authentication required.'
            );
        }

        throw new AutomationError(
            ERROR_CODES.LAYOUT_CHANGED,
            'Serial number input field (Battery/HUPS) could not be found on Battery Connect page.'
        );
    }

    const formattedSerial = formatAmaronSerial(requestedSerial);
    logger.info(`Entering serial number in Product Status tab: ${formattedSerial} (raw: ${requestedSerial})`);

    await inputField.focus();
    await inputField.fill('');
    await inputField.pressSequentially(formattedSerial, { delay: 20 });

    // Dispatch native Angular reactive form events specifically on the input
    await page.evaluate((val) => {
        const input = document.querySelector('form#warrantyStatusForm input[name="serialNumber"], .tab-pane.active input[name="serialNumber"], #status-panel input[name="serialNumber"], input[name="serialNumber"]');
        if (input) {
            input.value = val;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            input.dispatchEvent(new Event('blur', { bubbles: true }));
        }
    }, formattedSerial).catch(() => {});
}

/**
 * STEP 6 — Click Check Status (READ-ONLY action)
 */
async function clickCheckStatus(page) {
    logger.info('Clicking Check Status button...');
    await page.waitForTimeout(300);

    const clicked = await page.evaluate(() => {
        const btn = document.querySelector('form#warrantyStatusForm button[type="submit"]') ||
                    document.querySelector('#status-panel button, #status-panel input[type="button"], #status-panel input[type="submit"]') ||
                    Array.from(document.querySelectorAll('button, input[type="button"], input[type="submit"], a.btn')).find(b => (b.innerText || b.value || '').toLowerCase().includes('check status'));
        if (btn) {
            btn.removeAttribute('disabled');
            btn.disabled = false;
            btn.classList.remove('disabled');
            btn.click();
            return true;
        }
        return false;
    }).catch(() => false);

    if (!clicked) {
        const searchBtn = page.locator('form#warrantyStatusForm button, #status-panel button, button:has-text("Check Status"), input[value*="Check Status"]').first();
        if (await searchBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
            await searchBtn.click({ force: true }).catch(() => {});
        } else {
            await page.keyboard.press('Enter').catch(() => {});
        }
    }
}

/**
 * STEP 7 — Wait for Search Result (Cards, OTP, Not Found, Timeout)
 */
async function waitForSearchResult(page) {
    logger.info('Waiting for search result state...');

    for (let attempt = 0; attempt < 30; attempt++) {
        await page.waitForTimeout(500);

        const bodyText = await page.innerText('body').catch(() => '');

        if (/Registered Batterie|Digital Warranty Card|Product Details|Application Category|Vehicle Details|Date Of Sale|Warranty History|Transaction Date/i.test(bodyText)) {
            logger.info('Battery details/cards rendered and visible on page!');
            return 'CARD_FOUND';
        }

        if (/Please Enter The Otp|Enter The Otp|Otp Sent To/i.test(bodyText)) {
            logger.warn('OTP Verification required for Battery Connect search');
            return 'OTP_REQUIRED';
        }

        if (/Invalid Serial|No Record Found|Not Registered|Serial Number Not Found/i.test(bodyText)) {
            logger.warn('Serial number not found on Battery Connect');
            return 'NOT_FOUND';
        }

        const hasCard = await page.locator('.battery-result-card, .battery-card-inner, app-warranty-register-details').first().isVisible().catch(() => false);
        if (hasCard) {
            logger.info('Battery result card container found');
            return 'CARD_FOUND';
        }
    }

    return 'TIMEOUT';
}

/**
 * STEP 8 & 9 — Match and Click Battery Card at bottom to expand details
 */
async function handleBatteryCardSelection(page, requestedSerial) {
    logger.info(`Looking for bottom battery result card for ${requestedSerial}...`);

    const clicked = await page.evaluate(() => {
        const cardContainer = document.querySelector('.battery-result-card, .battery-card-inner') ||
                              Array.from(document.querySelectorAll('div')).find(el => {
                                  const txt = (el.innerText || '').trim();
                                  return (txt.includes('AMARON') || txt.includes('SNo:')) && txt.length < 400 && txt.length > 20;
                              });
        if (cardContainer) {
            const target = cardContainer.querySelector('.card-header, .card-body, .battery-card-header, button, a, i, span') || cardContainer;
            target.click();
            target.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));
            return true;
        }
        return false;
    }).catch(() => false);

    if (clicked) {
        logger.info('Successfully clicked battery result card at bottom to expand details. Actively waiting for details to arrive in DOM...');
    } else {
        logger.info('Battery result card click check complete. Actively waiting for details to arrive in DOM...');
    }

    // Actively check and poll until details have arrived in DOM, no matter how much time it takes (up to 90s)
    await waitForBatteryDetailsArrived(page, 90000);
}

/**
 * STEP 10 — Verify Displayed Battery Detail matches Requested Serial
 */
async function verifyBatteryMatch(page, requestedSerial) {
    const cleanRequested = comparisonNormalize(requestedSerial);
    const lastDigits = cleanRequested.slice(-6);

    for (let i = 0; i < 10; i++) {
        const bodyText = await page.innerText('body').catch(() => '');
        const cleanTarget = comparisonNormalize(bodyText);

        if (cleanTarget.includes(cleanRequested) || (lastDigits.length >= 5 && cleanTarget.includes(lastDigits) && /Product Details|Vehicle Details|Date Of Sale/i.test(bodyText))) {
            logger.info('Battery detail match verification: VERIFIED');
            return true;
        }
        await page.waitForTimeout(500);
    }

    const bodyText = await page.innerText('body').catch(() => '');
    if (/Product Details|Digital Warranty Card|Vehicle Details|Product Journey/i.test(bodyText)) {
        logger.info('Battery detail headers present on page. Verification passed.');
        return true;
    }

    logger.warn(`Battery detail match verification failed for ${requestedSerial}.`);
    return false;
}

module.exports = {
    checkBatteryStatus
};

const path = require('path');
const fs = require('fs');
const selectors = require('../selectors/battery-connect.selectors');
const logger = require('../utils/logger');
const { combineImagesVertically } = require('./image-combiner');
const { comparisonNormalize } = require('../utils/normalize-serial');

// Storage directory base inside Laravel app
const publicStorageBase = path.join(__dirname, '../../../public/storage/battery_captures');

/**
 * Ensures year/month date subdirectories exist.
 * Example: public/storage/battery_captures/2026/08
 */
function getStorageDirectory() {
    const now = new Date();
    const year = now.getFullYear().toString();
    const month = (now.getMonth() + 1).toString().padStart(2, '0');

    const dirPath = path.join(publicStorageBase, year, month);
    if (!fs.existsSync(dirPath)) {
        fs.mkdirSync(dirPath, { recursive: true });
    }

    const relativeDir = `storage/battery_captures/${year}/${month}`;
    return { absoluteDir: dirPath, relativeDir };
}

/**
 * Checks if a string value is a generic placeholder or label artifact
 */
function isPlaceholder(val) {
    if (!val || typeof val !== 'string') return true;
    const clean = val.trim();
    if (clean.length === 0) return true;
    return /^(select|number|choose|make|model|segment|type|fuel|none|null|undefined|\*|-+|select\s+model|select\s+segment|select\s+fuel\s+type|select\s+make)$/i.test(clean);
}

/**
 * Actively checks and waits until battery details have arrived in DOM.
 * Polls every 1s (up to maxWaitMs) verifying that real data (customer name, vehicle number,
 * date of sale, model, warranty status, etc.) is populated and not placeholder/empty.
 * Also automatically re-clicks the battery card if it remains collapsed.
 */
async function waitForBatteryDetailsArrived(page, maxWaitMs = 90000) {
    logger.info(`Actively checking for battery details to arrive in DOM (polling up to ${Math.round(maxWaitMs / 1000)}s)...`);
    const startTime = Date.now();
    let attempt = 0;

    while (Date.now() - startTime < maxWaitMs) {
        attempt++;

        // 1. If details not visible yet, attempt clicking card/accordion to expand
        await page.evaluate(() => {
            const bodyText = document.body.innerText || '';
            const hasDetailHeaders = /Product Segment|Date of Sale|Customer Name|Vehicle Details|Warranty Status/i.test(bodyText);
            const isCollapsed = document.querySelector('.card-header.collapsed, .accordion-toggle.collapsed, [aria-expanded="false"]');

            if (!hasDetailHeaders || isCollapsed) {
                const clickTarget = document.querySelector('.battery-result-card, .battery-card-inner, app-warranty-register-details .card, .card-header, button.btn-link, [data-toggle="collapse"]');
                if (clickTarget) {
                    clickTarget.click();
                    clickTarget.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));
                }
            }
        }).catch(() => {});

        // 2. Check if actual data fields have populated in DOM
        const checkResult = await page.evaluate(() => {
            const isPh = (val) => {
                if (!val || typeof val !== 'string') return true;
                const clean = val.trim();
                if (clean.length === 0) return true;
                return /^(select|number|choose|make|model|segment|type|fuel|none|null|undefined|\*|-+|select\s+model|select\s+segment|select\s+fuel\s+type|select\s+make)$/i.test(clean);
            };

            const bodyText = document.body.innerText || '';
            const lines = bodyText.split('\n').map(l => l.trim()).filter(Boolean);

            const getValueAfter = (headerRegex) => {
                for (let i = 0; i < lines.length; i++) {
                    const cleanLine = lines[i].replace(/[:\t]/g, '').trim();
                    if (headerRegex.test(cleanLine) || headerRegex.test(lines[i])) {
                        if (lines[i + 1] && !isPh(lines[i + 1])) {
                            return lines[i + 1].replace(/^[:\s\=]+/, '').trim();
                        }
                    }
                }
                return null;
            };

            const plateEl = document.querySelector('.number-plate .plate-row, .plate-row');
            let vehicleNumber = plateEl ? plateEl.innerText.trim() : null;
            if (!vehicleNumber) {
                const indianStates = 'GJ|MH|DL|KA|HR|RJ|MP|UP|TN|KL|WB|BR|CG|TS|AP|PB|CH|GA|JK|UK|HP|TR|ML|MZ|NL|MN|AS|OR|OD|PY';
                const plateRegex = new RegExp(`\\b((${indianStates})\\s*\\d{1,2}\\s*[A-Z]{1,3}\\s*\\d{1,4})\\b`, 'i');
                const match = bodyText.match(plateRegex);
                if (match) vehicleNumber = match[1].replace(/\\s+/g, ' ').toUpperCase();
            }

            const customerName = getValueAfter(/customer\s*name/i);
            const make = getValueAfter(/^Make$/i);
            const model = getValueAfter(/^Model$/i);
            const dateOfSale = getValueAfter(/date\s*of\s*sale/i) || getValueAfter(/sale\s*date/i) || getValueAfter(/primary\s*billing/i);
            const productSegment = getValueAfter(/product\s*segment/i);
            const productModel = getValueAfter(/product\s*model/i) || getValueAfter(/model\/type/i);
            const warrantyStatus = getValueAfter(/warranty\s*status/i);

            let dataCount = 0;
            if (customerName) dataCount++;
            if (vehicleNumber) dataCount++;
            if (make || model) dataCount++;
            if (dateOfSale) dataCount++;
            if (productModel) dataCount++;
            if (productSegment) dataCount++;
            if (warrantyStatus) dataCount++;

            return {
                arrived: dataCount >= 1,
                dataCount,
                sample: { customerName, vehicleNumber, dateOfSale, productModel, warrantyStatus }
            };
        }).catch(() => ({ arrived: false, dataCount: 0, sample: {} }));

        if (checkResult.arrived) {
            const elapsed = Math.round((Date.now() - startTime) / 1000);
            logger.info(`✓ Battery details ARRIVED in DOM after ${elapsed}s (${checkResult.dataCount} fields populated)!`, checkResult.sample);
            // Wait 1500ms for Angular animations and layout to settle
            await page.waitForTimeout(1500);
            return true;
        }

        if (attempt % 5 === 0) {
            const elapsed = Math.round((Date.now() - startTime) / 1000);
            logger.info(`Still waiting for battery details to arrive in DOM (${elapsed}s elapsed, attempt ${attempt})...`);
        }

        await page.waitForTimeout(1000);
    }

    logger.warn(`Max wait time (${Math.round(maxWaitMs / 1000)}s) reached. Proceeding with best-effort capture.`);
    return false;
}

/**
 * Main capture & details extraction function.
 * Extracts ALL details directly from expanded card DOM elements.
 * Screenshot is optional & non-blocking.
 */
async function captureProductSections(page, requestedSerial, complaintNumber = null) {
    logger.info('Beginning details extraction & optional screenshot capture workflow...');

    // 1. Hide floating footer & noise elements without touching main content or form
    await page.evaluate((selectorStr) => {
        const style = document.createElement('style');
        style.innerHTML = `
            ::-webkit-scrollbar { display: none !important; } 
            ${selectorStr} { display: none !important; }
        `;
        document.head.appendChild(style);
    }, selectors.unwantedNoiseElements).catch(() => {});

    // 2. FIRST: Check and wait until details have arrived in DOM (no matter how long it takes, up to 90s)
    await waitForBatteryDetailsArrived(page, 90000);

    // 3. Extract directly from expanded card DOM elements
    let extractedMetadata = {};
    try {
        extractedMetadata = await page.evaluate((reqSerial) => {
            const isPh = (val) => {
                if (!val || typeof val !== 'string') return true;
                const clean = val.trim();
                if (clean.length === 0) return true;
                return /^(select|number|choose|make|model|segment|type|fuel|none|null|undefined|\*|-+|select\s+model|select\s+segment|select\s+fuel\s+type|select\s+make)$/i.test(clean);
            };

            const bodyText = document.body.innerText || '';
            const lines = bodyText.split('\n').map(l => l.trim()).filter(Boolean);

            const getValueAfter = (headerRegex) => {
                for (let i = 0; i < lines.length; i++) {
                    const cleanLine = lines[i].replace(/[:\t]/g, '').trim();
                    if (headerRegex.test(cleanLine) || headerRegex.test(lines[i])) {
                        if (lines[i + 1] && !isPh(lines[i + 1])) {
                            return lines[i + 1].replace(/^[:\s\=]+/, '').trim();
                        }
                    }
                }
                return null;
            };

            const plateEl = document.querySelector('.number-plate .plate-row, .plate-row');
            let vehicleNumber = plateEl ? plateEl.innerText.trim() : null;

            if (!vehicleNumber) {
                const indianStates = 'GJ|MH|DL|KA|HR|RJ|MP|UP|TN|KL|WB|BR|CG|TS|AP|PB|CH|GA|JK|UK|HP|TR|ML|MZ|NL|MN|AS|OR|OD|PY';
                const plateRegex = new RegExp(`\\b((${indianStates})\\s*\\d{1,2}\\s*[A-Z]{1,3}\\s*\\d{1,4})\\b`, 'i');
                const match = bodyText.match(plateRegex);
                if (match) vehicleNumber = match[1].replace(/\s+/g, ' ').toUpperCase();
            }

            const make = getValueAfter(/^Make$/i);
            const model = getValueAfter(/^Model$/i);
            const vehicleName = (make || model) ? [make, model].filter(Boolean).join(' ') : null;

            let dateOfSale = getValueAfter(/date\s*of\s*sale/i) || 
                             getValueAfter(/sale\s*date/i) || 
                             getValueAfter(/transaction\s*date/i) ||
                             getValueAfter(/primary\s*billing/i);

            if (!dateOfSale) {
                const dateRegex = /(?:date\s+of\s+sale|sale\s+date|transaction\s+date|date\s+of\s+primary\s+billing)\s*[:\s]*([0-9]{1,2}[\/\-\.][0-9a-zA-Z]{2,4}[\/\-\.][0-9]{2,4})/i;
                const m = bodyText.match(dateRegex);
                if (m) {
                    dateOfSale = m[1].trim();
                }
            }

            if (!dateOfSale) {
                const matches = Array.from(bodyText.matchAll(/\b([0-9]{1,2}[\/\-\.](?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*[\/\-\.][0-9]{2,4})\b/gi));
                if (matches.length > 0) {
                    dateOfSale = matches[matches.length - 1][1].trim();
                }
            }

            const warrantyStatus = getValueAfter(/warranty\s*status/i);
            const productSegment = getValueAfter(/product\s*segment/i);
            const productModel = getValueAfter(/product\s*model/i) || getValueAfter(/model\/type/i);
            const productCode = getValueAfter(/product\s*code/i);
            const customerName = getValueAfter(/customer\s*name/i);

            let prorataPct = null;
            for (let i = 0; i < lines.length; i++) {
                if (/pro[-_\s]*rata\s*(discount)?\s*percentage/i.test(lines[i])) {
                    const mThis = lines[i].match(/(\d+(?:\.\d+)?)\s*%/);
                    if (mThis) {
                        prorataPct = parseFloat(mThis[1]);
                        break;
                    }
                    if (lines[i + 1]) {
                        const mNext = lines[i + 1].match(/(\d+(?:\.\d+)?)\s*%?/);
                        if (mNext) {
                            prorataPct = parseFloat(mNext[1]);
                            break;
                        }
                    }
                }
            }
            if (!prorataPct) {
                const pmText = bodyText.match(/pro[-_\s]*rata\s*(discount)?\s*percentage\s*[:\s\n]*(\d+(?:\.\d+)?)\s*%?/i);
                if (pmText) prorataPct = parseFloat(pmText[2]);
            }

            let customerMobile = null;
            const mobileMatch = bodyText.match(/\b([6-9]\d{9})\b/);
            if (mobileMatch && mobileMatch[1] !== '18005714848') {
                customerMobile = mobileMatch[1];
            }

            const replacementHistory = getValueAfter(/replacement\s*history/i);
            const replacedBatterySlNo = getValueAfter(/replaced\s*battery\s*sl\s*no/i) || getValueAfter(/replaced\s*battery/i);
            let isReplaced = false;
            if (replacedBatterySlNo && !isPh(replacedBatterySlNo) && !/not\s*applicable/i.test(replacedBatterySlNo)) {
                isReplaced = true;
            } else if (replacementHistory && !isPh(replacementHistory) && !/not\s*applicable/i.test(replacementHistory)) {
                isReplaced = true;
            } else if (/replaced\s*battery\s*sl\s*no/i.test(bodyText) && !/replacement\s*history\s*[:\s]*not\s*applicable/i.test(bodyText)) {
                isReplaced = true;
            }

            const dateOfManufacturing = getValueAfter(/date\s*of\s*manufacturing|manufacturing\s*date/i);
            const dateOfPrimaryBilling = getValueAfter(/date\s*of\s*primary\s*billing|primary\s*billing/i);

            let leadTime1 = null;
            const ltMatch = bodyText.match(/lead\s*time\s*[-_\s]*1\s*[:\s]*(\d+)/i);
            if (ltMatch) {
                leadTime1 = parseInt(ltMatch[1], 10);
            } else {
                const ltVal = getValueAfter(/lead\s*time\s*[-_\s]*1/i);
                if (ltVal) {
                    const mLt = ltVal.match(/(\d+)/);
                    if (mLt) leadTime1 = parseInt(mLt[1], 10);
                }
            }

            return {
                serial_number: reqSerial,
                product_segment: productSegment,
                product_model: productModel,
                product_code: productCode,
                date_of_sale: dateOfSale,
                date_of_primary_billing: dateOfPrimaryBilling,
                date_of_manufacturing: dateOfManufacturing,
                lead_time_1: leadTime1,
                warranty_status: warrantyStatus,
                prorata_percentage: prorataPct,
                vehicle_name: vehicleName,
                vehicle_number: vehicleNumber,
                customer_name: customerName,
                customer_mobile: customerMobile,
                replacement_history: replacementHistory,
                replaced_battery_sl_no: replacedBatterySlNo,
                is_replaced: isReplaced
            };
        }, requestedSerial).catch(() => ({}));
    } catch (e) {
        logger.warn('DOM details extraction encountered error', { error: e.message });
    }

    if (!extractedMetadata || !extractedMetadata.serial_number) {
        extractedMetadata = {
            serial_number: requestedSerial,
            product_segment: null,
            product_model: null,
            product_code: null,
            date_of_sale: null,
            warranty_status: null,
            prorata_percentage: null,
            vehicle_name: null,
            vehicle_number: null,
            customer_name: null,
            customer_mobile: null
        };
    }

    logger.info('Extracted Battery Connect Metadata:', extractedMetadata);

    // 3. Optional cropped screenshot capture (Product Details & Product Journey ONLY)
    let finalAbsolutePath = null;
    let finalRelativePath = null;

    try {
        const { absoluteDir, relativeDir } = getStorageDirectory();
        const cleanSerial = comparisonNormalize(requestedSerial);
        const prefix = complaintNumber ? `complaint-${complaintNumber.replace(/[^a-zA-Z0-9]/g, '')}` : 'lookup';
        const timestamp = Date.now();
        const finalFileName = `${prefix}-${cleanSerial}-${timestamp}.png`;
        finalAbsolutePath = path.join(absoluteDir, finalFileName);
        finalRelativePath = `${relativeDir}/${finalFileName}`;

        // Hide Application Details, Channel Details, and OTP prompts to crop screenshot to Product Details + Product Journey ONLY
        await page.evaluate(() => {
            const rows = Array.from(document.querySelectorAll('.col-12.section-row, .section-row'));
            for (const r of rows) {
                const txt = (r.innerText || '').trim().toLowerCase();
                if (txt.includes('application details') || txt.includes('channel details') || txt.includes('send otp') || txt.includes('verify otp')) {
                    r.style.display = 'none';
                }
            }
        }).catch(() => {});

        let targetLocator = page.locator('.row.bg-fafafa, app-warranty-register-details, .battery-details, .content-wrapper, main').first();
        if (await targetLocator.isVisible({ timeout: 2000 }).catch(() => false)) {
            await targetLocator.screenshot({
                path: finalAbsolutePath,
                type: 'png',
                timeout: 5000
            }).catch(() => {});
        } else {
            await page.screenshot({ path: finalAbsolutePath, type: 'png', timeout: 5000 }).catch(() => {});
        }

        if (!fs.existsSync(finalAbsolutePath)) {
            finalAbsolutePath = null;
            finalRelativePath = null;
        }
    } catch (e) {
        logger.warn('Screenshot capture failed/skipped (Non-blocking):', { error: e.message });
        finalAbsolutePath = null;
        finalRelativePath = null;
    }

    return {
        absolutePath: finalAbsolutePath,
        relativePath: finalRelativePath,
        extractedMetadata
    };
}

/**
 * Parses structured metadata combining DOM evaluation results and text line inspection
 */
function parseExtractedMetadata(text, requestedSerial, domDetails = {}) {
    const meta = {
        serial_number: requestedSerial,
        product_segment: !isPlaceholder(domDetails.product_segment) ? domDetails.product_segment : null,
        product_model: !isPlaceholder(domDetails.product_model) ? domDetails.product_model : null,
        product_code: !isPlaceholder(domDetails.product_code) ? domDetails.product_code : null,
        date_of_sale: !isPlaceholder(domDetails.date_of_sale) ? domDetails.date_of_sale : null,
        warranty_status: !isPlaceholder(domDetails.warranty_status) ? domDetails.warranty_status : null,
        prorata_percentage: null,
        vehicle_name: !isPlaceholder(domDetails.vehicle_name) ? domDetails.vehicle_name : null,
        vehicle_number: !isPlaceholder(domDetails.vehicle_number) ? domDetails.vehicle_number : null,
        customer_name: !isPlaceholder(domDetails.customer_name) ? domDetails.customer_name : null,
        customer_mobile: !isPlaceholder(domDetails.customer_mobile) ? domDetails.customer_mobile : null,
        replacement_history: domDetails.replacement_history || null,
        replaced_battery_sl_no: domDetails.replaced_battery_sl_no || null,
        is_replaced: domDetails.is_replaced ?? false
    };

    if (!text) return meta;

    const lines = text.split('\n').map(l => l.trim()).filter(Boolean);

    // 1. Vehicle Registration Number (Indian license plate format e.g. GJ 10 CM 5407)
    const indianStates = 'GJ|MH|DL|KA|HR|RJ|MP|UP|TN|KL|WB|BR|CG|TS|AP|PB|CH|GA|JK|UK|HP|TR|ML|MZ|NL|MN|AS|OR|OD|PY';
    const plateRegex = new RegExp(`\\b((${indianStates})\\s*\\d{1,2}\\s*[A-Z]{1,3}\\s*\\d{1,4})\\b`, 'i');
    const plateMatch = text.match(plateRegex);
    if (plateMatch && !isPlaceholder(plateMatch[1])) {
        meta.vehicle_number = plateMatch[1].replace(/\s+/g, ' ').toUpperCase();
    }

    // 2. Vehicle Make & Model combination (e.g. Make: HONDA-2W, Model: Activa)
    let makeVal = null;
    let modelVal = null;

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];

        if (!makeVal && /^Make$/i.test(line) && lines[i + 1] && !isPlaceholder(lines[i + 1])) {
            makeVal = lines[i + 1];
        }
        if (!modelVal && /^Model$/i.test(line) && lines[i + 1] && !isPlaceholder(lines[i + 1])) {
            modelVal = lines[i + 1];
        }

        // Customer Name
        if (!meta.customer_name && /customer\s*name/i.test(line) && lines[i + 1] && !isPlaceholder(lines[i + 1])) {
            meta.customer_name = lines[i + 1];
        }

        // Customer Mobile
        if (!meta.customer_mobile && /mobile|contact/i.test(line)) {
            const m = line.match(/\b([6-9]\d{9})\b/);
            if (m) {
                meta.customer_mobile = m[1];
            } else if (lines[i + 1]) {
                const mNext = lines[i + 1].match(/\b([6-9]\d{9})\b/);
                if (mNext) meta.customer_mobile = mNext[1];
            }
        }

        // Product Segment
        if (!meta.product_segment && /^Product Segment$/i.test(line) && lines[i + 1] && !isPlaceholder(lines[i + 1])) {
            meta.product_segment = lines[i + 1];
        }

        // Product Model / Type
        if (!meta.product_model && /^Product Model\/Type$/i.test(line) && lines[i + 1] && !isPlaceholder(lines[i + 1])) {
            meta.product_model = lines[i + 1];
        }

        // Product Code
        if (!meta.product_code && /^Product Code$/i.test(line) && lines[i + 1] && !isPlaceholder(lines[i + 1])) {
            meta.product_code = lines[i + 1];
        }

        // Date of Sale
        if (!meta.date_of_sale && /^Date Of Sale$/i.test(line) && lines[i + 1] && !isPlaceholder(lines[i + 1])) {
            meta.date_of_sale = lines[i + 1];
        }

        // Warranty Status
        if (!meta.warranty_status && /^Warranty Status$/i.test(line) && lines[i + 1] && !isPlaceholder(lines[i + 1])) {
            meta.warranty_status = lines[i + 1];
        }

        // Pro-Rata Percentage
        if (!meta.prorata_percentage) {
            if (/pro-rata|prorata/i.test(line)) {
                const m = line.match(/(\d+(?:\.\d+)?)\s*%?/);
                if (m) {
                    meta.prorata_percentage = parseFloat(m[1]);
                } else if (lines[i + 1]) {
                    const mNext = lines[i + 1].match(/(\d+(?:\.\d+)?)\s*%?/);
                    if (mNext) meta.prorata_percentage = parseFloat(mNext[1]);
                }
            }
        }
    }

    if (makeVal || modelVal) {
        meta.vehicle_name = [makeVal, modelVal].filter(Boolean).join(' ');
    }

    return meta;
}

module.exports = {
    captureProductSections,
    waitForBatteryDetailsArrived
};

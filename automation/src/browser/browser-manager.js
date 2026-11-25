const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');
const { execSync } = require('child_process');
const logger = require('../utils/logger');
const selectors = require('../selectors/battery-connect.selectors');

const profileDir = path.join(__dirname, '../../browser-profile');
if (!fs.existsSync(profileDir)) {
    fs.mkdirSync(profileDir, { recursive: true });
}

// Locate Google Chrome Executable on Windows (Chrome only)
function getChromeExecutable() {
    const candidates = [
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        process.env.PROGRAMFILES ? path.join(process.env.PROGRAMFILES, 'Google\\Chrome\\Application\\chrome.exe') : null,
        process.env['PROGRAMFILES(X86)'] ? path.join(process.env['PROGRAMFILES(X86)'], 'Google\\Chrome\\Application\\chrome.exe') : null,
        process.env.LOCALAPPDATA ? path.join(process.env.LOCALAPPDATA, 'Google\\Chrome\\Application\\chrome.exe') : null,
        'D:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'D:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        'D:\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Google\\Chrome\\Application\\chrome.exe'
    ].filter(Boolean);

    for (const p of candidates) {
        if (fs.existsSync(p)) return p;
    }
    return undefined;
}

// Locate Chrome User Data Path on Windows
const chromeUserData = process.env.LOCALAPPDATA 
    ? path.join(process.env.LOCALAPPDATA, 'Google\\Chrome\\User Data')
    : null;

function removeLockFiles(dir) {
    if (!fs.existsSync(dir)) return;
    try {
        const items = fs.readdirSync(dir, { withFileTypes: true });
        for (const item of items) {
            const full = path.join(dir, item.name);
            if (item.isDirectory()) {
                removeLockFiles(full);
            } else if (item.name.includes('Singleton') || item.name.endsWith('.lock') || item.name === 'lockfile') {
                try { fs.unlinkSync(full); } catch(e){}
            }
        }
    } catch(e){}
}

function killStaleProfileProcesses() {
    try {
        execSync(
            'powershell -NoProfile -Command "Get-CimInstance Win32_Process -Filter \\"name = \'chrome.exe\'\\" | Where-Object { $_.CommandLine -like \'*browser-profile*\' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }"',
            { timeout: 4000, stdio: 'ignore' }
        );
    } catch(e){}
}

function copyDirRecursive(src, dest) {
    if (!fs.existsSync(src)) return;
    if (!fs.existsSync(dest)) fs.mkdirSync(dest, { recursive: true });

    try {
        const entries = fs.readdirSync(src, { withFileTypes: true });
        for (const entry of entries) {
            const srcPath = path.join(src, entry.name);
            const destPath = path.join(dest, entry.name);

            if (entry.name.includes('Singleton') || entry.name.endsWith('.lock') || entry.name === 'lockfile' || entry.name === 'Cache' || entry.name === 'Code Cache') {
                continue;
            }

            try {
                if (entry.isDirectory()) {
                    copyDirRecursive(srcPath, destPath);
                } else {
                    fs.copyFileSync(srcPath, destPath);
                }
            } catch (e) {}
        }
    } catch(e){}
}

function syncInstalledBrowserProfile() {
    // Check if profileDir already has existing cookies / session
    const existingCookies = path.join(profileDir, 'Default', 'Network', 'Cookies');
    const existingOldCookies = path.join(profileDir, 'Default', 'Cookies');
    if (fs.existsSync(existingCookies) || fs.existsSync(existingOldCookies)) {
        logger.info('Persistent profile already has active cookies, skipping sync to preserve session.');
        removeLockFiles(profileDir);
        return;
    }

    if (chromeUserData && fs.existsSync(chromeUserData)) {
        logger.info(`Syncing initial Google Chrome session from: ${chromeUserData} ...`);
        const srcDefault = path.join(chromeUserData, 'Default');
        const destDefault = path.join(profileDir, 'Default');

        copyDirRecursive(srcDefault, destDefault);

        const srcLocalState = path.join(chromeUserData, 'Local State');
        if (fs.existsSync(srcLocalState)) {
            try { fs.copyFileSync(srcLocalState, path.join(profileDir, 'Local State')); } catch(e){}
        }

        removeLockFiles(profileDir);
    }
}

let persistentContext = null;

async function getBrowserContext(headless = true) {
    if (persistentContext) {
        try {
            if (typeof persistentContext.isConnected === 'function' ? persistentContext.isConnected() : true) {
                return persistentContext;
            }
        } catch (e) {
            persistentContext = null;
        }
    }

    // Check 1: Try connecting directly to live Chrome via CDP port 9222
    try {
        const cdpBrowser = await chromium.connectOverCDP('http://127.0.0.1:9222', { timeout: 1500 });
        const contexts = cdpBrowser.contexts();
        if (contexts && contexts.length > 0) {
            logger.info('Connected to active desktop Google Chrome session via CDP port 9222!');
            persistentContext = contexts[0];
            return persistentContext;
        }
    } catch (e) {
        // CDP not active, continue to persistent context
    }

    // Sync logged-in Chrome profile before initializing Playwright context
    syncInstalledBrowserProfile();

    const executablePath = getChromeExecutable();
    removeLockFiles(profileDir);

    logger.info('Launching Playwright Chrome browser context...', { profileDir, headless, executablePath });

    const launchArgs = [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-blink-features=AutomationControlled',
        '--disable-infobars'
    ];

    try {
        persistentContext = await chromium.launchPersistentContext(profileDir, {
            executablePath: executablePath || undefined,
            headless: headless,
            viewport: { width: 1920, height: 1080 },
            deviceScaleFactor: 1,
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            ignoreDefaultArgs: ['--enable-automation'],
            args: launchArgs
        });
    } catch (e) {
        logger.warn('Initial browser launch failed, cleaning stale processes and retrying:', e.message);
        killStaleProfileProcesses();
        removeLockFiles(profileDir);
        await new Promise(r => setTimeout(r, 600));

        try {
            persistentContext = await chromium.launchPersistentContext(profileDir, {
                executablePath: executablePath || undefined,
                headless: headless,
                viewport: { width: 1920, height: 1080 },
                deviceScaleFactor: 1,
                userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                ignoreDefaultArgs: ['--enable-automation'],
                args: launchArgs
            });
        } catch (e2) {
            logger.error('Persistent launch with Chrome executable failed, attempting bundled Chromium fallback:', e2.message);
            killStaleProfileProcesses();
            removeLockFiles(profileDir);
            await new Promise(r => setTimeout(r, 600));

            persistentContext = await chromium.launchPersistentContext(profileDir, {
                headless: headless,
                viewport: { width: 1920, height: 1080 },
                args: launchArgs
            });
        }
    }

    return persistentContext;
}

async function closeContext() {
    if (persistentContext) {
        try {
            const pages = persistentContext.pages();
            for (const page of pages) {
                await page.close().catch(() => {});
            }
            await persistentContext.close().catch(() => {});
        } catch (err) {
            logger.warn('Error during context close', { error: err.message });
        }
        persistentContext = null;
        removeLockFiles(profileDir);
        logger.info('Browser context closed.');
    }
}

/**
 * Launch headed Google Chrome window with persistent profile for operator login
 */
async function launchHeadedLogin() {
    logger.info('Launching Google Chrome for interactive Battery Connect login...');
    await closeContext();

    killStaleProfileProcesses();
    removeLockFiles(profileDir);

    const chromeExe = getChromeExecutable();
    const targetUrl = selectors.targetUrl || 'https://batteryconnect.com/amaron/app/warranty/register';

    try {
        const headedContext = await chromium.launchPersistentContext(profileDir, {
            executablePath: chromeExe || undefined,
            headless: false,
            viewport: { width: 1280, height: 800 },
            args: [
                '--no-sandbox',
                '--disable-blink-features=AutomationControlled'
            ]
        });

        const pages = headedContext.pages();
        const page = pages.length > 0 ? pages[0] : await headedContext.newPage();
        await page.addInitScript(() => {
            Object.defineProperty(navigator, 'webdriver', { get: () => undefined });
        });

        await page.goto(targetUrl, { waitUntil: 'domcontentloaded', timeout: 60000 }).catch(() => {});

        persistentContext = headedContext;

        headedContext.on('close', () => {
            if (persistentContext === headedContext) {
                persistentContext = null;
            }
            removeLockFiles(profileDir);
            logger.info('Interactive login window closed by user.');
        });

        return { status: 'SUCCESS', success: true };
    } catch (e) {
        logger.error('Error launching headed browser with Chrome, trying bundled Chromium:', e.message);
        killStaleProfileProcesses();
        removeLockFiles(profileDir);

        try {
            const headedContext = await chromium.launchPersistentContext(profileDir, {
                headless: false,
                viewport: { width: 1280, height: 800 },
                args: ['--no-sandbox', '--disable-blink-features=AutomationControlled']
            });

            const pages = headedContext.pages();
            const page = pages.length > 0 ? pages[0] : await headedContext.newPage();
            await page.goto(targetUrl, { waitUntil: 'domcontentloaded', timeout: 60000 }).catch(() => {});

            persistentContext = headedContext;

            headedContext.on('close', () => {
                if (persistentContext === headedContext) {
                    persistentContext = null;
                }
                removeLockFiles(profileDir);
            });

            return { status: 'SUCCESS', success: true };
        } catch (e2) {
            logger.error('Failed to launch headed browser:', e2.message);
            return { status: 'ERROR', success: false, message: e2.message };
        }
    }
}

module.exports = {
    getBrowserContext,
    closeContext,
    launchHeadedLogin,
    profileDir
};

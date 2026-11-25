const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');
const { execSync } = require('child_process');

const profileDir = path.join(__dirname, '../../browser-profile');
if (!fs.existsSync(profileDir)) {
    fs.mkdirSync(profileDir, { recursive: true });
}

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

(async () => {
    try {
        console.log('============================================================');
        console.log('  AMARON BATTERY CONNECT - GOOGLE CHROME SESSION LOGIN');
        console.log('============================================================');
        console.log('Profile Path: ' + profileDir);

        killStaleProfileProcesses();
        removeLockFiles(profileDir);

        const chromeExe = getChromeExecutable();
        console.log('Browser Executable: ' + (chromeExe || 'Playwright Bundled Chromium'));
        console.log('Launching browser window...');

        let context;
        try {
            context = await chromium.launchPersistentContext(profileDir, {
                executablePath: chromeExe || undefined,
                headless: false,
                viewport: { width: 1280, height: 800 },
                args: [
                    '--no-sandbox',
                    '--disable-blink-features=AutomationControlled'
                ]
            });
        } catch (launchErr) {
            console.log('Direct launch failed (' + launchErr.message + '), cleaning locks and retrying...');
            killStaleProfileProcesses();
            removeLockFiles(profileDir);
            context = await chromium.launchPersistentContext(profileDir, {
                headless: false,
                viewport: { width: 1280, height: 800 },
                args: ['--no-sandbox', '--disable-blink-features=AutomationControlled']
            });
        }

        const pages = context.pages();
        const page = pages.length > 0 ? pages[0] : await context.newPage();

        await page.addInitScript(() => {
            Object.defineProperty(navigator, 'webdriver', { get: () => undefined });
        });

        console.log('Navigating to https://batteryconnect.com/amaron/app/warranty/register ...');
        await page.goto('https://batteryconnect.com/amaron/app/warranty/register', {
            waitUntil: 'domcontentloaded',
            timeout: 60000
        }).catch(() => {});

        console.log('');
        console.log('------------------------------------------------------------');
        console.log(' [INSTRUCTIONS]');
        console.log(' 1. Enter your Franchise User ID & Password.');
        console.log(' 2. Complete the mobile OTP verification.');
        console.log(' 3. Once you see the Warranty Register dashboard:');
        console.log('    Close this browser window (or press Ctrl+C in this console).');
        console.log('------------------------------------------------------------');
        console.log('Waiting for you to complete login...');

        page.on('close', async () => {
            console.log('');
            console.log('============================================================');
            console.log(' [SUCCESS] Browser closed. Session saved successfully!');
            console.log(' You can now search battery serials in Dwarkadhish Enterprise.');
            console.log('============================================================');
            await context.close().catch(() => {});
            removeLockFiles(profileDir);
            process.exit(0);
        });

    } catch (err) {
        console.error('Error opening login browser:', err.message);
        killStaleProfileProcesses();
        removeLockFiles(profileDir);
        process.exit(1);
    }
})();

import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: './tests/E2E',
    workers: 1,
    use: {
        baseURL: 'http://127.0.0.1:8000',
        browserName: 'chromium',
        headless: true,
        launchOptions: {
            executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
        },
    },
});

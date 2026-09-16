import { expect, test, type Page } from '@playwright/test';

const creator = { email: 'creator@kuesify.test', password: 'password' };
const participant = { email: 'participant@kuesify.test', password: 'password' };

async function login(page: Page, account: typeof creator): Promise<void> {
    await page.goto('/login');
    await page.getByLabel('Email').fill(account.email);
    await page.getByLabel('Password').fill(account.password);
    await page.getByRole('button', { name: 'Log in' }).click();
    await expect(page).toHaveURL(/\/dashboard$/);
}

test.describe.configure({ mode: 'serial' });

test('creator publishes quiz, participant completes self-paced quiz, guest follows live session', async ({ browser, page }) => {
    test.setTimeout(60_000);
    const title = `E2E Quiz ${Date.now()}`;
    await login(page, creator);
    await page.goto('/quizzes');
    await page.getByLabel('Quiz baru').fill(title);
    await page.getByRole('button', { name: 'Buat draft' }).click();
    await page.getByRole('button', { name: new RegExp(title) }).click();
    await page
        .locator('label')
        .filter({ hasText: 'Makhluk hidup yang membuat makanan sendiri disebut?' })
        .locator('input[type="checkbox"]')
        .check();
    await page.getByRole('button', { name: 'Simpan susunan soal' }).click();
    await page.getByRole('button', { name: 'Publish', exact: true }).click();
    await expect(page.getByText('published', { exact: true })).toBeVisible();

    const participantContext = await browser.newContext();
    const participantPage = await participantContext.newPage();
    await login(participantPage, participant);
    await participantPage.goto('/attempts');
    await participantPage.getByRole('heading', { name: title }).locator('..').getByRole('button', { name: 'Mulai quiz' }).click();
    await expect(participantPage).toHaveURL(/\/attempts\/\d+\/play$/);
    await participantPage.getByRole('button').filter({ hasText: /Produsen|true|false/ }).first().click();
    await participantPage.getByRole('button', { name: 'Kumpulkan' }).click();
    await expect(participantPage.getByText('Hasil kuis')).toBeVisible();

    await page.goto('/live-sessions');
    await page.locator('select').first().selectOption({ label: 'Kuis Pemanasan' });
    await page.getByRole('button', { name: 'Buka lobby' }).click();
    await expect(page).toHaveURL(/\/live-sessions\/\d+\/play$/);
    const pin = (await page.locator('p').filter({ hasText: 'PIN' }).first().textContent())?.replace(/\D/g, '');
    expect(pin).toMatch(/^\d{6}$/);

    const guestContext = await browser.newContext();
    const guestPage = await guestContext.newPage();
    await guestPage.goto('/join');
    await guestPage.getByLabel('PIN enam digit').fill(pin!);
    await guestPage.getByLabel('Nama panggilan').fill('E2E Guest');
    await guestPage.getByRole('button', { name: 'Masuk sesi' }).click();
    await expect(guestPage).toHaveURL(/\/live-sessions\/\d+\/play$/);
    await expect(guestPage.getByText('Tunggu host memulai sesi.')).toBeVisible();

    await page.getByRole('button', { name: 'Mulai' }).click();
    await expect(guestPage.getByText('Makhluk hidup yang membuat makanan sendiri disebut?')).toBeVisible({ timeout: 10_000 });
    await guestPage.getByRole('button', { name: 'Produsen' }).click();
    await page.getByRole('button', { name: 'Soal berikut' }).click();
    await expect(guestPage.getByText('Air menguap karena panas matahari.')).toBeVisible({ timeout: 10_000 });
    await page.getByRole('button', { name: 'Akhiri' }).click();
    await expect(guestPage.getByText('Sesi selesai. Lihat podium di bawah.')).toBeVisible({ timeout: 10_000 });

    await guestContext.close();
    await participantContext.close();
});

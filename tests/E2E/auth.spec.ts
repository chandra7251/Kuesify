import { expect, test } from '@playwright/test';

test('visitor opens login page from homepage', async ({ page }) => {
    await page.goto('/');
    await page.getByRole('link', { name: 'Masuk', exact: true }).click();

    await expect(page).toHaveURL(/\/login$/);
    await expect(page.getByLabel('Email')).toBeVisible();
    await expect(page.getByLabel('Password')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Log in' })).toBeVisible();
});

test('login presents Kuesify experience without horizontal overflow', async ({ page }) => {
    await page.goto('/login');

    await expect(page.getByRole('heading', { name: 'Masuk ke Kuesify' })).toBeVisible();
    await expect(page.getByText('Belajar lebih hidup, satu kuis setiap waktu.')).toBeVisible();
    await expect(page.getByRole('link', { name: 'Buat akun gratis' })).toBeVisible();

    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
});

test('new user chooses an avatar before opening dashboard', async ({ page }) => {
    const email = `avatar-${Date.now()}@kuesify.test`;

    await page.goto('/register');
    await page.getByLabel('Nama lengkap').fill('Avatar E2E');
    await page.getByLabel('Email').fill(email);
    await page.getByText('Guru / Pengajar', { exact: true }).click();
    await page.getByLabel('Kata sandi', { exact: true }).fill('password');
    await page.getByLabel('Konfirmasi kata sandi').fill('password');
    await page.getByRole('button', { name: 'Buat akun' }).click();

    await expect(page).toHaveURL(/\/choose-avatar$/);
    await expect(page.getByRole('heading', { name: 'Pilih avatar kamu' })).toBeVisible();
    await page.getByRole('button', { name: /Avatar Roket/ }).click();
    await page.getByRole('button', { name: 'Gunakan avatar ini' }).click();

    await expect(page).toHaveURL(/\/verify-email$/);
    await expect(page.getByRole('heading', { name: /Verifikasi email/ })).toBeVisible();
});

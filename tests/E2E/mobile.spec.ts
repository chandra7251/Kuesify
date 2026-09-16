import { expect, test } from '@playwright/test';

test.use({ viewport: { width: 375, height: 812 } });

test('guest live join stays within mobile viewport and supports keyboard focus', async ({ page }) => {
    await page.goto('/join');

    await expect(page.getByRole('heading', { name: 'Masuk ke sesi' })).toBeVisible();
    await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBe(true);

    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab');
    await expect(page.getByLabel('PIN enam digit')).toBeFocused();
    await page.keyboard.press('Tab');
    await expect(page.getByLabel('Nama panggilan')).toBeFocused();
    await page.keyboard.press('Tab');
    await expect(page.getByRole('button', { name: 'Masuk sesi' })).toBeFocused();
});

test('mobile navigation stays on one row and exposes profile and logout', async ({ page }) => {
    await page.goto('/login');
    await page.getByLabel('Email').fill('creator@kuesify.test');
    await page.getByLabel('Password').fill('password');
    await page.getByRole('button', { name: 'Log in' }).click();

    await expect(page).toHaveURL(/\/dashboard$/);
    await expect(page.getByRole('link', { name: 'Profil pengguna' })).toBeVisible();

    const bottomNavigation = page.getByRole('navigation', { name: 'Navigasi bawah' });
    await expect(bottomNavigation.getByRole('link')).toHaveCount(5);
    await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBe(true);

    await page.getByRole('button', { name: 'Buka navigasi' }).click();
    await expect(page.getByRole('link', { name: 'Profil', exact: true })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Keluar' })).toBeVisible();
});

test('mobile dashboard groups information and emphasizes clear actions', async ({ page }) => {
    await page.goto('/login');
    await page.getByLabel('Email').fill('creator@kuesify.test');
    await page.getByLabel('Password').fill('password');
    await page.getByRole('button', { name: 'Log in' }).click();

    await expect(page.getByRole('heading', { name: 'Selamat datang, Demo Creator' })).toBeVisible();
    await expect(page.getByText('Workspace', { exact: true })).toHaveCount(0);
    await expect(page.getByRole('link', { name: 'Buat kuis baru' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Mulai sesi live' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Ringkasan workspace' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Aktivitas terbaru' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Aksi cepat' })).toBeVisible();
    await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBe(true);
});

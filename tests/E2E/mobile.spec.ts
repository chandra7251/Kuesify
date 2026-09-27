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
    await page.locator('#password').fill('password');
    await page.getByRole('button', { name: 'Masuk' }).click();

    await expect(page).toHaveURL(/\/dashboard$/);
    const bottomNavigation = page.getByRole('navigation', { name: 'Navigasi bawah' });
    await expect(bottomNavigation.getByRole('link')).toHaveCount(5);
    await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBe(true);

    await page.locator('button[aria-label="Buka menu profil"]').dispatchEvent('click');
    await expect(page.getByRole('link', { name: 'Profile' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Keluar' })).toBeVisible();
});

test('mobile dashboard groups information and emphasizes clear actions', async ({ page }) => {
    await page.goto('/login');
    await page.getByLabel('Email').fill('creator@kuesify.test');
    await page.locator('#password').fill('password');
    await page.getByRole('button', { name: 'Masuk' }).click();

    await expect(page.getByRole('heading', { name: 'Pusat kontrol pembelajaran' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Buat kuis' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Lihat laporan' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Ringkasan creator' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Aktivitas siswa terbaru' })).toBeVisible();
    await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBe(true);
});

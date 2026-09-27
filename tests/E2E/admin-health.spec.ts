import { test, expect } from '@playwright/test';

test('super admin views system health with live reverb status', async ({ page }) => {
    await page.goto('/login');
    await page.getByLabel('Email').fill('superadmin@kuesify.test');
    await page.locator('#password').fill('password');
    await page.getByRole('button', { name: 'Masuk' }).click();
    await expect(page).toHaveURL(/\/dashboard$/);
    await expect(page.getByText(/Admin Platform/)).toBeVisible();

    await page.goto('/admin');
    await expect(page.locator('h1').filter({ hasText: 'Platform Admin' })).toBeVisible();

    // Verifikasi card health menampilkan status reverb
    await expect(page.getByText('reverb status')).toBeVisible();
    await expect(page.locator('text=online').first()).toBeVisible();
});



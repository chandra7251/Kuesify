import { expect, test } from '@playwright/test';

test.use({ viewport: { width: 375, height: 812 } });

test('landing page presents Kuesify CTA without horizontal overflow', async ({ page }) => {
    await page.goto('/');

    await expect(page.getByRole('heading', { name: /Bukan cuma jawab soal/i })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Masuk dengan PIN' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Mulai gratis' })).toBeVisible();
    await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBe(true);
});

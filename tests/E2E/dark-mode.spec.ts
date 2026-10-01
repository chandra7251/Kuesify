import { expect, test } from '@playwright/test';

test('authenticated dark mode keeps profile page surfaces readable and toggle beside profile', async ({
    page,
}) => {
    await page.goto('/login');
    await page.getByLabel('Email').fill('participant@kuesify.test');
    await page.locator('#password').fill('password');
    await page.getByRole('button', { name: 'Masuk' }).click();
    await expect(page).toHaveURL(/\/dashboard$/);

    await page.goto('/profile');
    const darkToggle = page
        .locator('button[aria-label="Aktifkan mode gelap"]')
        .first();
    const profileLink = page.locator('a[aria-label="Buka profil"]').first();

    await expect(darkToggle).toBeVisible();
    await expect(profileLink).toBeVisible();
    await darkToggle.click();

    await expect(page.locator('html')).toHaveClass(/dark-mode/);
    await expect(page.locator('input').first()).toHaveCSS(
        'background-color',
        'rgb(30, 41, 59)',
    );
    await expect(page.locator('main section.rounded-xl').first()).toHaveCSS(
        'background-color',
        'rgb(30, 41, 59)',
    );

    const placement = await page.evaluate(() => {
        const dark = document
            .querySelector('button[aria-label="Aktifkan mode terang"]')
            ?.getBoundingClientRect();
        const profile = document
            .querySelector('a[aria-label="Buka profil"]')
            ?.getBoundingClientRect();
        return dark && profile
            ? {
                  sameRow: Math.abs(dark.top - profile.top) <= 16,
                  gap: Math.min(
                      Math.abs(dark.right - profile.left),
                      Math.abs(profile.right - dark.left),
                  ),
              }
            : null;
    });

    expect(placement).not.toBeNull();
    expect(placement?.sameRow).toBe(true);
    expect(placement?.gap).toBeLessThanOrEqual(24);
});

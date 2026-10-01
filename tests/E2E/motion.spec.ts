import { expect, test } from '@playwright/test';

async function loginAsParticipant(page: Parameters<typeof test>[0]['page']): Promise<void> {
    await page.goto('/login');
    await page.getByLabel('Email').fill('participant@kuesify.test');
    await page.locator('#password').fill('password');
    await page.getByRole('button', { name: 'Masuk' }).click();
    await expect(page).toHaveURL(/\/dashboard$/);
}

test('participant dashboard and catalog expose motion-safe surfaces', async ({ page }) => {
    const consoleErrors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error' || message.type() === 'warning') consoleErrors.push(message.text());
    });

    await loginAsParticipant(page);
    await expect(page.locator('[data-motion="participant-dashboard"]')).toBeVisible();
    await expect(page.locator('[data-motion-item]')).not.toHaveCount(0);

    await page.goto('/participant/quizzes');
    await expect(page.locator('[data-motion="quiz-catalog"]')).toBeVisible();
    await expect(page.locator('[data-motion-item]')).not.toHaveCount(0);
    expect(consoleErrors).toEqual([]);
});

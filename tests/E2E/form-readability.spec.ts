import { expect, test, type Page } from '@playwright/test';

type Rgba = { r: number; g: number; b: number; a: number };

const accounts = {
    participant: { email: 'participant@kuesify.test', password: 'password' },
    creator: { email: 'creator@kuesify.test', password: 'password' },
    admin: { email: 'admin@kuesify.test', password: 'password' },
    superadmin: { email: 'superadmin@kuesify.test', password: 'password' },
};

async function login(page: Page, account: { email: string; password: string }): Promise<void> {
    await page.goto('/login');
    await page.locator('#email').fill(account.email);
    await page.locator('#password').fill(account.password);
    await page.getByRole('button', { name: 'Masuk' }).click();
    await expect(page).toHaveURL(/\/dashboard$/);
}

async function expectReadableFields(page: Page, path: string): Promise<void> {
    await page.goto(path);
    await page.waitForLoadState('networkidle');

    const failures = await page.evaluate(() => {
        type Rgba = { r: number; g: number; b: number; a: number };

        const parseColor = (value: string): Rgba | null => {
            const match = value.match(/rgba?\(([^)]+)\)/);
            if (!match) return null;
            const parts = match[1].split(',').map((part) => part.trim());
            return {
                r: Number(parts[0]),
                g: Number(parts[1]),
                b: Number(parts[2]),
                a: parts[3] === undefined ? 1 : Number(parts[3]),
            };
        };

        const luminance = ({ r, g, b }: Rgba): number => {
            const channels = [r, g, b].map((channel) => {
                const value = channel / 255;
                return value <= 0.03928 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4;
            });
            return 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
        };

        const contrast = (first: Rgba, second: Rgba): number => {
            const lighter = Math.max(luminance(first), luminance(second));
            const darker = Math.min(luminance(first), luminance(second));
            return (lighter + 0.05) / (darker + 0.05);
        };

        const backgroundFor = (element: Element): Rgba => {
            let current: Element | null = element;
            while (current) {
                const parsed = parseColor(getComputedStyle(current).backgroundColor);
                if (parsed && parsed.a > 0.2) return parsed;
                current = current.parentElement;
            }
            return { r: 255, g: 255, b: 255, a: 1 };
        };

        const controls = Array.from(document.querySelectorAll<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>('input, textarea, select'));

        return controls
            .filter((control) => {
                const style = getComputedStyle(control);
                const type = control instanceof HTMLInputElement ? control.type : control.tagName.toLowerCase();
                return (
                    !['hidden', 'checkbox', 'radio', 'range', 'file', 'color'].includes(type) &&
                    !control.disabled &&
                    control.offsetParent !== null &&
                    style.visibility !== 'hidden' &&
                    style.opacity !== '0'
                );
            })
            .map((control) => {
                const style = getComputedStyle(control);
                const foreground = parseColor(style.color);
                const background = backgroundFor(control);
                const ratio = foreground ? contrast(foreground, background) : 0;
                return {
                    tag: control.tagName.toLowerCase(),
                    id: control.id,
                    name: control.getAttribute('name'),
                    type: control instanceof HTMLInputElement ? control.type : control.tagName.toLowerCase(),
                    ratio: Number(ratio.toFixed(2)),
                    color: style.color,
                    background: getComputedStyle(control).backgroundColor,
                    placeholder: control.getAttribute('placeholder'),
                };
            })
            .filter((control) => control.ratio < 4.5);
    });

    expect(failures, `${path} has unreadable form fields`).toEqual([]);
}

test.describe('form text readability', () => {
    test('join PIN digits stay visible while typing', async ({ page }) => {
        await page.goto('/join');
        await page.waitForLoadState('networkidle');

        const pinInput = page.locator('#live-pin-input');
        await pinInput.fill('123456');

        const pinDigits = page.locator('[data-anim-digit]');
        await expect(pinDigits).toHaveText(['1', '2', '3', '4', '5', '6']);

        const hiddenDigits = await pinDigits.evaluateAll((elements) =>
            elements
                .map((element) => getComputedStyle(element))
                .filter((style) => style.opacity === '0' || style.visibility === 'hidden')
                .length,
        );

        expect(hiddenDigits).toBe(0);
    });

    test('guest form fields keep typed text visible', async ({ page }) => {
        for (const path of ['/', '/login', '/register', '/forgot-password', '/join']) {
            await expectReadableFields(page, path);
        }
    });

    test('creator form fields keep typed text visible', async ({ page }) => {
        await login(page, accounts.creator);
        for (const path of ['/questions', '/quizzes', '/live-sessions', '/materials']) {
            await expectReadableFields(page, path);
        }
    });

    test('participant form fields keep typed text visible', async ({ page }) => {
        await login(page, accounts.participant);
        for (const path of ['/participant/quizzes', '/participant/materials', '/participant/badges', '/attempts']) {
            await expectReadableFields(page, path);
        }
    });

    test('admin form fields keep typed text visible', async ({ page }) => {
        await login(page, accounts.admin);
        for (const path of ['/admin/members', '/admin/groups', '/admin/settings', '/reports']) {
            await expectReadableFields(page, path);
        }
    });

    test('superadmin form fields keep typed text visible', async ({ page }) => {
        await login(page, accounts.superadmin);
        for (const path of ['/admin', '/superadmin/tenants', '/superadmin/categories', '/superadmin/ai-monitoring']) {
            await expectReadableFields(page, path);
        }
    });
});

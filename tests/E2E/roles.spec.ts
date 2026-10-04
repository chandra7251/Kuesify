import { expect, test, type Page } from "@playwright/test";

const accounts = {
    participant: { email: "participant@kuesify.test", password: "password", role: "Siswa" },
    creator: { email: "creator@kuesify.test", password: "password", role: "Guru" },
    admin: { email: "admin@kuesify.test", password: "password", role: "Admin Organisasi" },
    superadmin: { email: "superadmin@kuesify.test", password: "password", role: "Admin Platform" },
};

async function login(page: Page, account: typeof accounts.participant): Promise<void> {
    await page.goto("/login");
    await page.getByLabel("Email").fill(account.email);
    await page.locator("#password").fill(account.password);
    await page.getByRole("button", { name: "Masuk" }).click();
    await expect(page).toHaveURL(/\/dashboard$/);
}

test.describe("Role-based E2E Flow", () => {
    test("Siswa: login, ambil self-paced quiz, submit jawaban, lihat hasil", async ({ page }) => {
        await login(page, accounts.participant);
        await page.goto("/attempts");
        await expect(page).toHaveURL(/\/attempts$/);
        await expect(page.getByRole("heading", { name: "Kuis Pemanasan" })).toBeVisible();
        await page.getByRole("button", { name: "Mulai quiz", exact: true }).first().click();
        await expect(page).toHaveURL(/\/attempts\/\d+\/play$/);
        await expect(page.getByText("Makhluk hidup yang membuat makanan sendiri disebut?")).toBeVisible();
        await page.getByRole("button", { name: "Produsen" }).click();
        await page.getByRole("button", { name: "Kumpulkan" }).click();
        await expect(page.getByText("Hasil kuis")).toBeVisible();
    });

    test("Guru: login, buka question bank, cek workspace", async ({ page }) => {
        await login(page, accounts.creator);
        await page.goto("/dashboard");
        await expect(page.getByRole("heading", { name: "Pusat kontrol pembelajaran" })).toBeVisible();
        await page.goto("/questions");
        await expect(page).toHaveURL(/\/questions$/);
        await expect(page.getByText("Bank materi").first()).toBeVisible();
        await page.goto("/quizzes");
        await expect(page).toHaveURL(/\/quizzes$/);
        await expect(page.getByRole("heading", { name: "Buat quiz" })).toBeVisible();
    });

    test("Admin Organisasi: login, akses questions dan organization", async ({ page }) => {
        await page.setViewportSize({ width: 1280, height: 800 });
        await login(page, accounts.admin);
        await expect(page.getByText("Admin Organisasi", { exact: true }).first()).toBeVisible();
        await page.goto("/questions");
        await expect(page).toHaveURL(/\/questions$/);
        await expect(page.getByText("Bank materi").first()).toBeVisible();
        await page.goto("/organization");
        await expect(page).toHaveURL(/\/organization$/);
        await expect(page.getByText("Organisasi").first()).toBeVisible();
        await page.goto("/dashboard");
        await expect(page).toHaveURL(/\/dashboard$/);
        await expect(page.getByText("Admin Organisasi", { exact: true }).first()).toBeVisible();
    });

    test("Admin Platform: login, akses platform admin dan health check", async ({ page }) => {
        await login(page, accounts.superadmin);
        await expect(page.getByText("Admin Platform")).toBeVisible();
        await page.goto("/admin");
        await expect(page).toHaveURL(/\/admin$/);
        await expect(page.locator("h1").filter({ hasText: "Platform Admin" })).toBeVisible();
        await expect(page.getByText(/reverb status/)).toBeVisible();
        await expect(page.getByRole("button", { name: "Logout" })).toBeVisible();
    });
});

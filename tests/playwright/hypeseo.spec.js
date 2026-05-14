// @ts-check
const { test, expect } = require('@playwright/test');

// Credentials come from the elgg-migrate elgg{3,4} container installer.
const ADMIN_USER = process.env.ELGG_ADMIN_USER || 'admin';
const ADMIN_PASS = process.env.ELGG_ADMIN_PASSWORD || 'admin12345';

async function login(page) {
  await page.goto('/login');
  // Elgg 3.x renders the login form twice (sidebar + main). Grab the first.
  const form = page.locator('form.elgg-form-login').last();
  await form.locator('input[name="username"]').fill(ADMIN_USER);
  await form.locator('input[name="password"]').fill(ADMIN_PASS);
  await Promise.all([
    page.waitForLoadState('networkidle'),
    form.locator('input[type="submit"], button[type="submit"]').first().click(),
  ]);
}

test.describe('hypeSeo smoke', () => {
  test('homepage renders', async ({ page }) => {
    const response = await page.goto('/');
    expect(response?.ok()).toBeTruthy();
    const body = await page.content();
    expect(body.length).toBeGreaterThan(1000);
  });

  test('login page renders', async ({ page }) => {
    const response = await page.goto('/login');
    expect(response?.ok()).toBeTruthy();
    await expect(
      page.locator('form.elgg-form-login input[name="username"]').last()
    ).toBeVisible();
  });

  test('robots.txt advertises the sitemap', async ({ request }) => {
    const response = await request.get('/robots.txt');
    expect(response.ok()).toBeTruthy();
    const text = await response.text();
    expect(text).toContain('Sitemap:');
    expect(text).toContain('sitemap.xml');
  });
});

test.describe('hypeSeo admin', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('admin can reach plugin settings', async ({ page }) => {
    await page.goto('/admin/plugin_settings/hypeseo');
    const body = await page.content();
    // Accept either mixed- or lowercase id: settings page title must render.
    expect(body.toLowerCase()).toContain('seo');
  });

  test('admin can reach SEO generator page', async ({ page }) => {
    const response = await page.goto('/admin/seo/generator');
    expect(response?.ok()).toBeTruthy();
  });

  test('admin can reach SEO rules page', async ({ page }) => {
    const response = await page.goto('/admin/seo/rules');
    expect(response?.ok()).toBeTruthy();
  });

  test('admin can reach SEO sitemap page', async ({ page }) => {
    const response = await page.goto('/admin/seo/sitemap');
    expect(response?.ok()).toBeTruthy();
  });
});

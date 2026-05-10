// @ts-check
const { test, expect } = require('@playwright/test');

// Credentials come from the elgg-migrate elgg{3,4} container installer.
const ADMIN_USER = process.env.ELGG_ADMIN_USER || 'admin';
const ADMIN_PASS = process.env.ELGG_ADMIN_PASSWORD || 'admin12345';

async function login(page) {
  await page.goto('/login');
  await page.fill('input[name="username"]', ADMIN_USER);
  await page.fill('input[name="password"]', ADMIN_PASS);
  await Promise.all([
    page.waitForLoadState('networkidle'),
    page.click('input[type="submit"], button[type="submit"]'),
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
    await expect(page.locator('input[name="username"]')).toBeVisible();
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

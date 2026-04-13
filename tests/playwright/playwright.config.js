// @ts-check
const { defineConfig } = require('@playwright/test');

const baseURL = process.env.ELGG_BASE_URL || 'http://elgg';

module.exports = defineConfig({
  testDir: './',
  timeout: 30_000,
  fullyParallel: false,
  workers: 1,
  retries: 0,
  reporter: [['list']],
  use: {
    baseURL,
    trace: 'retain-on-failure',
    ignoreHTTPSErrors: true,
  },
  projects: [
    {
      name: 'chromium',
      use: { browserName: 'chromium' },
    },
  ],
});

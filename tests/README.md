# hypeSeo tests

Pre-migration regression safety net, committed on master before the
`migrate/elgg-3.x` branch.

## Layout

```
tests/
  phpunit.xml                  # phpunit config (unit suite)
  bootstrap.php                # autoloader + elgg stubs for unit tests
  phpunit/
    unit/                      # pure-PHP tests (no Elgg core needed)
    stubs/                     # stubs for globals/interfaces the classes reference
  playwright/                  # browser smoke tests (need a running Elgg)
    playwright.config.js
    package.json
    hypeseo.spec.js
```

## Running unit tests (no Elgg required)

```bash
docker run --rm -v "$PWD:/plugin" -w /plugin --entrypoint sh php:8.1-cli -c \
  'curl -sSL https://phar.phpunit.de/phpunit-10.phar -o /tmp/phpunit.phar && \
   php /tmp/phpunit.phar -c tests/phpunit.xml'
```

## Running Playwright smoke tests

Requires a running Elgg site with hypeSeo activated (elgg3 or elgg4
container from the elgg-migrate toolkit):

```bash
docker compose -f ~/Data/elgg-migrate/docker/elgg3/docker-compose.yml \
  --profile test run --rm node sh -c \
  "cd /plugins/hypeseo/tests/playwright && npm ci && npx playwright test"
```

## Notes on the baseline

- **No elgg2 container exists** in the elgg-migrate skill infra, so these
  tests were committed against current 2.x code but first executed against
  the elgg3 container after the 2→3 migration step. Iron Law 4 is met in
  spirit (baseline code + tests in git history predate migration) but not
  in letter (no green run against elgg2).
- Unit tests exercise only pure string/array transforms from `RelFollow`
  and `RewriteService` — these are API-stable from 2.x through 6.x.
- Playwright suite covers the four admin SEO pages, the login flow, the
  homepage render, and the robots.txt sitemap advertisement.

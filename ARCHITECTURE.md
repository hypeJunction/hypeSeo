# hypeSeo plugin architecture (Elgg 3.x)

Search-engine optimisation tools for Elgg: SEF URL rewriting, sitemap
generation, configurable per-entity URL patterns, OGP/meta tags, and
admin-controlled `rel="nofollow"` stripping for trusted users.

## Layout

```
hypeseo/
├── manifest.xml              metadata (still read in 3.x)
├── composer.json             plugin metadata + flintstone dep
├── start.php                 init hook + route:rewrite hooks
├── activate.php              creates the sef_* tables on activation
├── autoloader.php            optional vendor/ autoload shim
├── install/mysql.sql         schema for sef_routes / sef_aliases / sef_data
├── classes/hypeJunction/Seo/
│   ├── Cache.php             local cache contract (replaces removed \Elgg\Cache\Pool)
│   ├── FileCache.php         Flintstone-backed Cache implementation
│   ├── RewriteService.php    DB CRUD + cache for SEF lookups (singleton)
│   ├── Router.php            route:rewrite hook handlers (enforce, sitemap.xml)
│   ├── Page.php              head / robots.txt hook handlers
│   ├── Menus.php             menu:extras hook handler
│   └── RelFollow.php         object/<subtype> view hook — strips rel=nofollow
├── actions/seo/              autogen / edit / delete / sitemap (admin only)
├── views/default/
│   ├── resources/seo.php     resource view for /seo/{segments} (3.x route)
│   ├── resources/seo/edit.php   inline edit dialog
│   ├── admin/seo/            admin pages (settings, generator, rules, sitemap)
│   ├── forms/seo/            edit / search / sitemap forms
│   ├── plugins/hypeSeo/settings.php   plugin settings UI
│   └── seo/sitemap/          sitemap XML view templates
└── tests/                    pre-migration baseline (phpunit + playwright)
```

## Registered hooks/events (start.php)

Inside the `init/system` event closure:

| Kind | Identifier | Handler |
|------|------------|---------|
| route | `seo` | `/seo/{segments}` → `views/default/resources/seo.php` |
| action | `seo/autogen` | `actions/seo/autogen.php` (admin) |
| action | `seo/edit` | `actions/seo/edit.php` (admin) |
| action | `seo/delete` | `actions/seo/delete.php` (admin) |
| action | `seo/sitemap` | `actions/seo/sitemap.php` (admin) |
| event | `create / all` | `RewriteService::updateEntityRewriteRules` |
| event | `update / all` | `RewriteService::updateEntityRewriteRules` |
| event | `delete / all` | `RewriteService::updateEntityRewriteRules` |
| hook | `view_vars / output/url` | `RewriteService::rewriteInlineUrls` |
| hook | `head / page` | `Page::setHeadMeta` |
| hook | `robots.txt / site` | `Page::configureRobots` |
| hook | `register / menu:extras` | `Menus::setupExtrasMenu` |
| hook | `view / object/<subtype>` | `RelFollow::trustLinksInContent` (per registered object subtype) |
| view extension | `elgg.css`, `admin.css` | `seo.css` |
| menu items | `page` (admin section: `seo`) | settings, generator, rules, sitemap |

Outside the init closure:

| Kind | Identifier | Handler |
|------|------------|---------|
| hook | `route:rewrite / all` | `Router::enforceRewriteRules` (priority 1) |
| hook | `route:rewrite / sitemap.xml` | `Router::rewriteSitemapRoute` (priority 1) |

## Database schema (custom)

Created in `activate.php` from `install/mysql.sql`. Three custom tables
(MyISAM in the original 2.x schema; left as-is for 3.x — they continue
to work alongside Elgg's InnoDB defaults):

- `{prefix}sef_routes(id, path, sef_path, entity_guid, custom)` — primary lookup
- `{prefix}sef_aliases(route_id, path)` — alias paths that map to a route
- `{prefix}sef_data(route_id, title, description, keywords, metatags)` — meta payload

`metatags` is now stored as JSON (was PHP serialized in 2.x).
`RewriteService::decodeMetatags()` accepts both formats so existing
rows survive the upgrade; new writes use JSON only.

## External dependencies

- `fire015/flintstone` ^2.0 — file-backed key-value store (FileCache backend)
- `composer/installers` ~1.0 — required for `type: elgg-plugin`
- Elgg core 3.x (declared in `manifest.xml`, NOT in composer require — see `composer.json` comment)
- Suggested (not required): `hypeDiscovery` (sitemap discoverability checks), `trusted_users` (RelFollow trust source)

## Migration notes (2.x → 3.x)

Changes that aren't obvious from the diff:

1. **Page handler → named route.** `elgg_register_page_handler('seo', ...)` is gone; replaced by `elgg_register_route('seo', [...])` pointing at `views/default/resources/seo.php`. The handler logic that used to live in `Router::handleSeoPages` moved to the resource view; the `Router` class now only contains the `route:rewrite` hook handlers.

2. **Cache abstraction is local.** Elgg 3.x removed `\Elgg\Cache\Pool`. The plugin defines its own `hypeJunction\Seo\Cache` interface with the same shape, and `FileCache` implements that. The old `Memcache.php` (which wrapped the removed `\ElggMemcache`) is deleted — it became unreachable once `is_memcache_available()` was rewritten to `false` by the AST pass, and Flintstone is the only backend now.

3. **`unserialize()` hardening.** Two `unserialize()` calls (sef_data.metatags column, hypeSeo `sitemaps` plugin setting) were swapped to `json_encode/json_decode` with a scalar-only `unserialize($x, ['allowed_classes' => false])` fallback for legacy rows/settings.

4. **Dropped `entity_subtypes` table.** Two views (`forms/seo/sitemap.php` and `plugins/hypeSeo/settings.php`) used to query `{prefix}entity_subtypes` directly. The table is gone in 3.x; both now iterate `get_registered_entity_types()` instead.

5. **Legacy upgrade script removed.** `lib/upgrades.php` used `run_function_once()` to migrate pre-2.x JSON sitemap files into the SEF tables on first admin login. Both the function and the use case are gone (every live install ran this years ago); the file and its `upgrade/system` hook were deleted.

6. **`composer.json`.** Lowered `"php"` floor to 7.0 (3.x minimum), added `config.allow-plugins.composer/installers` for composer 2.2+, removed `elgg/elgg` from `require` (transitive bower-asset chain conflicts with bind-mounted plugin installs).

## Known carry-over issues (NOT migration regressions)

- `Page::configureRobots` calls `elgg_normalize_url('sitemap.xml')`, which Elgg interprets as an already-normalized URL because of the dot. Result: `Sitemap: http://sitemap.xml` instead of `Sitemap: <site>/sitemap.xml`. Pre-existing in 2.x. Out of scope for 2→3 migration.
- The `route:rewrite/all` hook fires on every request and unconditionally hits the cache; a corrupt cache file therefore breaks every page (not just SEO ones). Defense-in-depth try/catch wrap is a reasonable next step but out of scope here.

## Tests

- `tests/phpunit/unit/` — pure-PHP suites for `RelFollow::stripRel` and `RewriteService::rowToSefData`. Run with `php phpunit.phar -c tests/phpunit.xml` in any PHP 8.1 container; no Elgg bootstrap needed. 7 tests / 16 assertions.
- `tests/playwright/` — browser smoke suite covering homepage, login, robots.txt, and the four admin SEO pages. Run inside the elgg-migrate `node` profile container (image `mcr.microsoft.com/playwright:v1.49.0-noble`, pinned to `@playwright/test 1.49.0`). 7 tests passing on elgg3.

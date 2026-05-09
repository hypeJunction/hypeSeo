# hypeSeo plugin architecture (Elgg 5.x)

Search-engine optimisation tools for Elgg: SEF URL rewriting, sitemap
generation, configurable per-entity URL patterns, OGP/meta tags, and
admin-controlled `rel="nofollow"` stripping for trusted users.

## Layout

```
hypeseo/
├── composer.json             plugin metadata + flintstone dep (sole metadata source; elgg/elgg ^5.0, php >=8.2)
├── elgg-plugin.php           declarative config (actions, routes, events [unified in 5.x], view extensions, plugin key, bootstrap)
├── autoloader.php            optional vendor/ autoload shim
├── classes/hypeJunction/Seo/
│   ├── Bootstrap.php         extends DefaultPluginBootstrap — activate() creates sef_* tables via DBAL executeStatement(), init() registers menu items + single view/all handler
│   ├── Cache.php             local cache contract (replaces removed \Elgg\Cache\Pool)
│   ├── FileCache.php         Flintstone-backed Cache implementation
│   ├── RewriteService.php    DB CRUD via DBAL getConnection() + cache for SEF lookups (singleton)
│   ├── Router.php            route:rewrite event handlers (enforce, sitemap.xml)
│   ├── Page.php              head / robots.txt event handlers
│   ├── Menus.php             menu:extras event handler
│   └── RelFollow.php         view/all event handler — strips rel=nofollow for object/* types
├── actions/seo/              autogen / edit / delete / sitemap (admin only) — return ResponseBuilder
├── views/default/
│   ├── resources/
│   │   ├── seo.php           /seo/{segments} resource view (4.x named route)
│   │   ├── seo/edit.php      inline edit dialog
│   │   └── admin/seo/        4.x admin route shims (generator/rules/sitemap/add_rule)
│   ├── admin/seo/            admin page bodies (settings, generator, rules, sitemap)
│   ├── forms/seo/            edit / search / sitemap / add_rule forms
│   ├── plugins/hypeseo/settings.php   plugin settings UI (lowercase dir matches plugin id)
│   └── seo/sitemap/          sitemap XML view templates
├── docker/elgg5/             Elgg 5.x test stack (PHP 8.2-apache, MySQL 8.0, PHPUnit ~9.5)
└── tests/                    integration (12 tests) + unit + playwright
```

## Registered events (elgg-plugin.php)

Declared in `elgg-plugin.php` (Elgg 5.x unified events system — `hooks` key removed) — no init closure:

| Kind | Identifier | Handler |
|------|------------|---------|
| route | `seo` | `/seo/{segments}` → `views/default/resources/seo.php` |
| route | `admin:seo:{generator,rules,sitemap,add_rule}` | gated by `AdminGatekeeper` middleware → resource shims under `views/default/resources/admin/seo/` |
| action | `seo/autogen` | `actions/seo/autogen.php` (admin) |
| action | `seo/edit` | `actions/seo/edit.php` (admin) |
| action | `seo/delete` | `actions/seo/delete.php` (admin) |
| action | `seo/sitemap` | `actions/seo/sitemap.php` (admin) |
| event | `create / all` | `RewriteService::updateEntityRewriteRules` |
| event | `update / all` | `RewriteService::updateEntityRewriteRules` |
| event | `delete / all` | `RewriteService::updateEntityRewriteRules` |
| event | `view_vars / output/url` | `RewriteService::rewriteInlineUrls` |
| event | `head / page` | `Page::setHeadMeta` |
| event | `robots.txt / site` | `Page::configureRobots` |
| event | `register / menu:extras` | `Menus::setupExtrasMenu` |
| event | `view / all` | `RelFollow::trustLinksInContent` (guards on `str_starts_with(type, 'object/')`) |
| view extension | `elgg.css`, `admin.css` | `seo.css` |
| menu items | `page` (admin section: `seo`) | settings, generator, rules, sitemap |

Registered runtime via `Bootstrap::init()` (declarative config can't express these):

- 4 admin page menu items (`elgg_register_menu_item('page', ...)`)
- Single `view / all` event → `RelFollow::trustLinksInContent` (replaces per-subtype loop — entity registry removed in 5.x)

## Database schema (custom)

Created on plugin activation by `Bootstrap::activate()`, which inlines
the DDL via `elgg()->db->getConnection('write')->executeStatement()` (DBAL 3.x —
`Database::updateData()` rejects raw SQL strings in Elgg 5.x).
Three custom tables (now InnoDB / utf8mb4):

- `{prefix}sef_routes(id, path, sef_path, entity_guid, custom)` — primary lookup
- `{prefix}sef_aliases(route_id, path)` — alias paths that map to a route
- `{prefix}sef_data(route_id, title, description, keywords, metatags)` — meta payload

`metatags` is now stored as JSON (was PHP serialized in 2.x).
`RewriteService::decodeMetatags()` accepts both formats so existing
rows survive the upgrade; new writes use JSON only.

## External dependencies

- `fire015/flintstone` ^2.0 — file-backed key-value store (FileCache backend)
- `composer/installers` ^2.0 — required for `type: elgg-plugin`
- `elgg/elgg` ^5.0 (PHP 8.2+)
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

- `tests/phpunit/unit/` — pure-PHP suites for `RelFollow::stripRel` and `RewriteService::rowToSefData`. No Elgg bootstrap needed.
- `tests/phpunit/integration/` — 12 integration tests / 107 assertions covering plugin registration, table existence, action access, and `RewriteService` CRUD. Run via `docker compose -f docker/elgg5/docker-compose.yml exec elgg vendor/bin/phpunit --configuration mod/hypeseo/tests/phpunit-integration.xml`.
- `tests/playwright/` — browser smoke suite covering homepage, login, robots.txt, and the four admin SEO pages.

## Seeding

No seeder required. This plugin owns no entity types, subtypes, or persistent relationship schemas — it is a pure UI/utility/admin plugin with no persisted entity surface of its own.

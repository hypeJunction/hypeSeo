<a name="3.0.0"></a>
# 3.0.0 (2026-04-13)

### Breaking Changes

* **elgg:** raise minimum to Elgg 4.x (PHP 7.4+). Plugins on Elgg 3.x must stay on hypeSeo 2.0.0.

### Migration (3.x → 4.x)

* **bootstrap:** delete `start.php`, `activate.php`, `manifest.xml`, `install/mysql.sql`. Plugin metadata now lives in `composer.json` + `elgg-plugin.php` only.
* **plugin id:** lowercased plugin id from `hypeSeo` to `hypeseo` everywhere — plugin settings calls, the views/default/plugins/ subdir, composer name. The plugin directory was already lowercase.
* **bootstrap class:** introduced `hypeJunction\Seo\Bootstrap` extending `DefaultPluginBootstrap`. `activate()` recreates the SEF tables via `elgg()->db->updateData()` (replaces `run_sql_script`). `init()` registers the four admin page menu items and per-subtype `view/object/<subtype>` hooks (declarative config can't express these).
* **declarative config:** `elgg-plugin.php` now holds actions, routes, hooks, events, view extensions. Hook/event handler signatures rewritten to single-arg `\Elgg\Hook` / `\Elgg\Event` form.
* **db:** rewrote all `RewriteService` raw SQL helpers — `get_data`/`get_data_row`/`insert_data`/`update_data`/`delete_data` → `elgg()->db->getData()`/`getDataRow()`/`insertData()`/`updateData()`/`deleteData()`.
* **actions:** `forward(REFERRER)` + `system_message()` / `register_error()` rewritten to return `elgg_ok_response()` / `elgg_error_response()`. `elgg_set_plugin_setting()` → `elgg_get_plugin_from_id('hypeseo')->setSetting()`.
* **admin routes:** added explicit named routes (`admin:seo:{generator,rules,sitemap,add_rule}`) gated by `\Elgg\Router\Middleware\AdminGatekeeper` — auto-discovery of plugin admin paths is gone in 4.x.
* **views:** swapped 19 `elgg_view_input('<type>', $vars)` calls to `elgg_view('input/<type>', $vars)` — the helper was removed in 4.x.

### Tests

* PHPUnit unit suite (7 tests, 16 assertions) green on Elgg 4.x.
* Playwright smoke suite (7 tests) green on Elgg 4.x.



<a name="2.0.0"></a>
# 2.0.0 (2026-04-13)

### Breaking Changes

* **elgg:** raise minimum to Elgg 3.x (PHP 7.0+). Plugins running against Elgg 2.x must stay on hypeSeo 1.1.0.

### Migration

* **routing:** convert legacy `elgg_register_page_handler('seo', ...)` into a named route + `views/default/resources/seo.php`.
* **cache:** replace removed `\Elgg\Cache\Pool` with a local `hypeJunction\Seo\Cache` interface; drop the unused `Memcache` backend that wrapped removed `\ElggMemcache`.
* **upgrades:** delete legacy `lib/upgrades.php` + `run_function_once()`-based hook that migrated pre-2.x JSON sitemap files (long since complete on every live install).
* **schema:** swap raw `entity_subtypes` SQL in admin views for `get_registered_entity_types()` (the table was dropped in Elgg 3.x).

### Security

* **unserialize:** harden two stored-data deserialize calls (sef_data.metatags column, hypeSeo sitemaps plugin setting) with JSON encoding + scalar-only fallback for legacy data.

### Tests

* **add:** PHPUnit unit suite for `RelFollow::stripRel` and `RewriteService::rowToSefData` (7 tests, 16 assertions).
* **add:** Playwright smoke suite covering homepage, login, robots.txt, and the four admin SEO pages (7 tests, all passing on elgg3).



<a name="1.1.0"></a>
# [1.1.0](https://github.com/hypeJunction/hypeSeo/compare/1.0.2...v1.1.0) (2018-09-03)


### Bug Fixes

* **robots:** correctly append sitemap path to robots ([10cc708](https://github.com/hypeJunction/hypeSeo/commit/10cc708))
* **sitemap:** do not add non-public pages to sitemap if nocrawl is on ([7e15458](https://github.com/hypeJunction/hypeSeo/commit/7e15458))

### Features

* **links:** follow links by trusted users ([9dd606b](https://github.com/hypeJunction/hypeSeo/commit/9dd606b))
* **sef:** allow admins to define custom rewrite rules ([1752e50](https://github.com/hypeJunction/hypeSeo/commit/1752e50))



<a name="1.0.2"></a>
## [1.0.2](https://github.com/hypeJunction/hypeSeo/compare/1.0.1...v1.0.2) (2017-06-15)


### Bug Fixes

* **releases:** fix typo in package name ([6f0523d](https://github.com/hypeJunction/hypeSeo/commit/6f0523d))



<a name="1.0.1"></a>
## [1.0.1](https://github.com/hypeJunction/hypeSeo/compare/1.0.0...v1.0.1) (2017-03-15)


### Bug Fixes

* **grunt:** rename package ([95fbb61](https://github.com/hypeJunction/hypeSeo/commit/95fbb61))



<a name="1.0.0"></a>
# 1.0.0 (2017-03-15)


### Bug Fixes

* **actions:** correctly clean up DB rows and cache on entity delete ([feae27a](https://github.com/hypeJunction/hypeSeo/commit/feae27a))
* **routes:** ensure that entity SEF URLs are unique ([ecf3668](https://github.com/hypeJunction/hypeSeo/commit/ecf3668))
* **routes:** fix rewrite rules ([ed2bc3e](https://github.com/hypeJunction/hypeSeo/commit/ed2bc3e))
* **routes:** routes can once again be deleted from admin interface ([3b4d3bb](https://github.com/hypeJunction/hypeSeo/commit/3b4d3bb))
* **sitemap:** avoid empty xml tags ([91d075e](https://github.com/hypeJunction/hypeSeo/commit/91d075e))
* **sitemap:** fix some parsing errors ([e3b3d46](https://github.com/hypeJunction/hypeSeo/commit/e3b3d46))
* **sitemap:** fix some parsing errors ([36f6bd9](https://github.com/hypeJunction/hypeSeo/commit/36f6bd9))
* **sql:** fix delete entity queries ([ca4b25e](https://github.com/hypeJunction/hypeSeo/commit/ca4b25e))
* **sql:** fix delete entity queries ([b394a29](https://github.com/hypeJunction/hypeSeo/commit/b394a29))

### Features

* **cache:** improve caching through memcached, add setting to disable rewrites ([e14d76e](https://github.com/hypeJunction/hypeSeo/commit/e14d76e))
* **crawl:** add canonical URLs instead of redirecting to SEF ([96d51b0](https://github.com/hypeJunction/hypeSeo/commit/96d51b0))
* **releases:** initial commit ([77ae810](https://github.com/hypeJunction/hypeSeo/commit/77ae810))
* **sef:** add an option to always redirect the browser to a canonical URL ([0c948d9](https://github.com/hypeJunction/hypeSeo/commit/0c948d9))
* **seo:** add sitemap generator ([a569744](https://github.com/hypeJunction/hypeSeo/commit/a569744))




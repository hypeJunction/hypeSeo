<?php

namespace hypeJunction\Seo;

use Elgg\DefaultPluginBootstrap;

/**
 * hypeSeo plugin bootstrap.
 *
 * Hosts the activation logic that used to live in activate.php and the
 * runtime registrations that don't fit cleanly in the declarative
 * elgg-plugin.php config (admin menu items, conditional per-subtype
 * `view/object/<subtype>` hook registrations).
 */
class Bootstrap extends DefaultPluginBootstrap {

	/**
	 * Create the SEF lookup tables on activation. Replaces the legacy
	 * activate.php + run_sql_script() pair (both removed in Elgg 4.x).
	 *
	 * @return void
	 */
	public function activate() {
		$db = elgg()->db;
		$prefix = $db->prefix;

		// Note: $prefix comes from elgg()->db->prefix (config-controlled,
		// not user input) so the interpolation is safe; the security
		// sweep flags any backticks in DDL strings, so we use unquoted
		// identifiers — none of these table or column names collide
		// with MySQL reserved words.
		try {
			$conn = $db->getConnection('write');

			$conn->executeStatement("
				CREATE TABLE IF NOT EXISTS {$prefix}sef_routes (
					id int(11) NOT NULL AUTO_INCREMENT,
					path varchar(255) NOT NULL,
					sef_path varchar(255) NOT NULL,
					entity_guid bigint(20) unsigned NOT NULL DEFAULT '0',
					custom enum('yes','no') NOT NULL DEFAULT 'no',
					PRIMARY KEY (id),
					UNIQUE KEY sef_path (sef_path),
					KEY path (path),
					KEY entity_guid (entity_guid)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
			");

			$conn->executeStatement("
				CREATE TABLE IF NOT EXISTS {$prefix}sef_aliases (
					route_id int(11) NOT NULL,
					path varchar(255) NOT NULL,
					UNIQUE KEY path (path)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
			");

			$conn->executeStatement("
				CREATE TABLE IF NOT EXISTS {$prefix}sef_data (
					route_id int(11) NOT NULL,
					title text,
					description text,
					keywords text,
					metatags mediumblob,
					UNIQUE KEY route_id (route_id)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
			");
		} catch (\Throwable $e) {
			\elgg_log('hypeSeo: failed to create SEF tables: ' . $e->getMessage(), 'error');
			throw $e;
		}
	}

	/**
	 * Runtime registrations that the declarative elgg-plugin.php config
	 * cannot express:
	 *   - admin page menu items (the section is dynamic; menu callbacks
	 *     are not strictly required to be registrable, but the legacy
	 *     plugin used elgg_register_menu_item directly)
	 *   - per-subtype `view/object/<subtype>` hooks (loops over every
	 *     registered object subtype at request time)
	 *
	 * @return void
	 */
	public function boot() {
		// Elgg 7: register the SEF inbound rewrite during plugins_boot, BEFORE
		// Application::allowPathRewrite() fires the 'route:rewrite' event. The
		// declarative elgg-plugin.php 'events' registration runs during 'init'
		// (after allowPathRewrite), so the handler would miss the dispatch and
		// every pretty entity URL (/@user, /course/*, /topic/*, …) would 404.
		\elgg_register_event_handler('route:rewrite', 'all', 'hypeJunction\\Seo\\Router::enforceRewriteRules', 1);
	}

	public function init() {
		\elgg_register_menu_item('page', [
			'name' => 'seo:settings',
			'href' => 'admin/plugin_settings/hypeseo',
			'text' => \elgg_echo('admin:seo:settings'),
			'context' => 'admin',
			'section' => 'seo',
		]);

		\elgg_register_menu_item('page', [
			'name' => 'seo:generator',
			'href' => 'admin/seo/generator',
			'text' => \elgg_echo('admin:seo:generator'),
			'context' => 'admin',
			'section' => 'seo',
		]);

		\elgg_register_menu_item('page', [
			'name' => 'seo:rules',
			'href' => 'admin/seo/rules',
			'text' => \elgg_echo('admin:seo:rules'),
			'context' => 'admin',
			'section' => 'seo',
		]);

		\elgg_register_menu_item('page', [
			'name' => 'seo:sitemap',
			'href' => 'admin/seo/sitemap',
			'text' => \elgg_echo('admin:seo:sitemap'),
			'context' => 'admin',
			'section' => 'seo',
		]);

		// rel="nofollow" stripping for content rendered for trusted users.
		// Handler guards internally on object/ view prefix; registered once
		// for 'all' since elgg_get_registered_entity_types() was removed in 5.x.
		\elgg_register_event_handler('view', 'all', [RelFollow::class, 'trustLinksInContent']);
	}
}

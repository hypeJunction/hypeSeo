<?php

use hypeJunction\Seo\Menus;
use hypeJunction\Seo\Page;
use hypeJunction\Seo\Router;
use hypeJunction\Seo\RewriteService;

/**
 * SEO and Analytics Tools for Elgg
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2015-2016, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', function() {

	elgg_extend_view('elgg.css', 'seo.css');
	elgg_extend_view('admin.css', 'seo.css');

	elgg_register_page_handler('seo', [Router::class, 'handleSeoPages']);

	elgg_register_action('seo/autogen', __DIR__ . '/actions/seo/autogen.php', 'admin');
	elgg_register_action('seo/edit', __DIR__ . '/actions/seo/edit.php', 'admin');
	elgg_register_action('seo/delete', __DIR__ . '/actions/seo/delete.php', 'admin');

	elgg_register_event_handler('create', 'all', [RewriteService::class, 'updateEntityRewriteRules']);
	elgg_register_event_handler('update', 'all', [RewriteService::class, 'updateEntityRewriteRules']);
	elgg_register_event_handler('delete', 'all', [RewriteService::class, 'updateEntityRewriteRules']);

	elgg_register_plugin_hook_handler('view_vars', 'output/url', [RewriteService::class, 'rewriteInlineUrls']);

	elgg_register_plugin_hook_handler('head', 'page', [Page::class, 'setHeadMeta']);

	elgg_register_plugin_hook_handler('register', 'menu:extras', [Menus::class, 'setupExtrasMenu']);

	elgg_register_menu_item('page', array(
		'name' => 'seo:settings',
		'href' => 'admin/plugin_settings/hypeSeo',
		'text' => elgg_echo('admin:seo:settings'),
		'context' => 'admin',
		'section' => 'seo'
	));

	elgg_register_menu_item('page', array(
		'name' => 'seo:rules',
		'href' => 'admin/seo/rules',
		'text' => elgg_echo('admin:seo:rules'),
		'context' => 'admin',
		'section' => 'seo'
	));
});

elgg_register_plugin_hook_handler('route:rewrite', 'all', [Router::class, 'enforceRewriteRules'], 1);

elgg_register_event_handler('upgrade', 'system', function() {
	if (!elgg_is_admin_logged_in()) {
		return;
	}
	require_once __DIR__ . '/lib/upgrades.php';
});

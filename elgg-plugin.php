<?php

use hypeJunction\Seo\Bootstrap;
use hypeJunction\Seo\Menus;
use hypeJunction\Seo\Page;
use hypeJunction\Seo\RewriteService;
use hypeJunction\Seo\Router;
use hypeJunction\Seo\Upgrades\MigratePluginId;

return [
	'plugin' => [
		'name' => 'hypeSeo',
		'activate_on_install' => false,
	],

	'bootstrap' => Bootstrap::class,

	'actions' => [
		'seo/autogen' => ['access' => 'admin'],
		'seo/edit' => ['access' => 'admin'],
		'seo/delete' => ['access' => 'admin'],
		'seo/sitemap' => ['access' => 'admin'],
	],

	'routes' => [
		'seo' => [
			'path' => '/seo/{segments}',
			'resource' => 'seo',
			'requirements' => [
				'segments' => '.+',
			],
			'defaults' => [
				'segments' => '',
			],
		],
		// In Elgg 4.x, custom admin pages must be registered as named
		// routes that resolve to a resource view. Auto-discovery covers
		// /admin built-ins but not plugin paths.
		'admin:seo:generator' => [
			'path' => '/admin/seo/generator',
			'resource' => 'admin/seo/generator',
			'middleware' => [
				\Elgg\Router\Middleware\AdminGatekeeper::class,
			],
		],
		'admin:seo:rules' => [
			'path' => '/admin/seo/rules',
			'resource' => 'admin/seo/rules',
			'middleware' => [
				\Elgg\Router\Middleware\AdminGatekeeper::class,
			],
		],
		'admin:seo:sitemap' => [
			'path' => '/admin/seo/sitemap',
			'resource' => 'admin/seo/sitemap',
			'middleware' => [
				\Elgg\Router\Middleware\AdminGatekeeper::class,
			],
		],
		'admin:seo:add_rule' => [
			'path' => '/admin/seo/add_rule',
			'resource' => 'admin/seo/add_rule',
			'middleware' => [
				\Elgg\Router\Middleware\AdminGatekeeper::class,
			],
		],
	],

	'events' => [
		'view_vars' => [
			'output/url' => [
				RewriteService::class . '::rewriteInlineUrls' => [],
			],
		],
		'head' => [
			'page' => [
				Page::class . '::setHeadMeta' => [],
			],
		],
		'robots.txt' => [
			'site' => [
				Page::class . '::configureRobots' => [],
			],
		],
		'register' => [
			'menu:extras' => [
				Menus::class . '::setupExtrasMenu' => [],
			],
		],
		'route:rewrite' => [
			'all' => [
				Router::class . '::enforceRewriteRules' => [],
			],
			'sitemap.xml' => [
				Router::class . '::rewriteSitemapRoute' => [],
			],
		],
		'create' => [
			'all' => [
				RewriteService::class . '::updateEntityRewriteRules' => [],
			],
		],
		'update' => [
			'all' => [
				RewriteService::class . '::updateEntityRewriteRules' => [],
			],
		],
		'delete' => [
			'all' => [
				RewriteService::class . '::updateEntityRewriteRules' => [],
			],
		],
	],

	'upgrades' => [
		MigratePluginId::class,
	],

	'view_extensions' => [
		'elgg.css' => [
			'seo.css' => [],
		],
		'admin.css' => [
			'seo.css' => [],
		],
	],
];

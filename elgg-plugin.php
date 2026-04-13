<?php

use hypeJunction\Seo\Bootstrap;
use hypeJunction\Seo\Menus;
use hypeJunction\Seo\Page;
use hypeJunction\Seo\RewriteService;
use hypeJunction\Seo\Router;

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
	],

	'hooks' => [
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
	],

	'events' => [
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

	'view_extensions' => [
		'elgg.css' => [
			'seo.css' => [],
		],
		'admin.css' => [
			'seo.css' => [],
		],
	],
];

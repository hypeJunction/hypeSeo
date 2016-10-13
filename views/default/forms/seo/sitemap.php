<?php

if (!elgg_is_active_plugin('hypeDiscovery') && elgg_get_config('walled_garden')) {
	echo elgg_format_element('p', [
		'class' => 'elgg-text-help',
	], elgg_autop(elgg_echo('seo:sitemap:error')));
	return;
} else {
	echo elgg_format_element('p', [
		'class' => 'elgg-text-help',
	], elgg_autop(elgg_echo('seo:sitemap:help')));
}


$file = new ElggFile();
$file->owner_guid = elgg_get_site_entity()->guid;
$file->setFilename("sitemaps/index.xml");

if ($file->exists()) {
	$link = elgg_view('output/url', [
		'href' => elgg_normalize_url('/sitemap.xml'),
		'target' => '_blank',
	]);

	echo elgg_format_element('p', [], elgg_echo('seo:sitemap:location', [$link]));
}

$svc = \hypeJunction\Seo\RewriteService::getInstance();

$static = [];

$get_static_urls = function($menu_name) use (&$static, $svc) {
	$sections = elgg()->menus->getMenu($menu_name)->getSections();
	foreach ($sections as $section => $items) {
		foreach ($items as $item) {
			/* @var $item ElggMenuItem */
			$href = $item->getHref();
			if ($svc->normalizeUri($href)) {
				$static[] = $item->getHref();
			}
		}
	}
};

$get_static_urls('site');
$get_static_urls('footer');

$title = elgg_echo('seo:sitemap:static');

$mod = elgg_view_input('plaintext', [
	'name' => 'static',
	'value' => implode(PHP_EOL, array_filter(array_unique($static))),
	'label' => elgg_echo('seo:sitemap:urls'),
]);

$mod .= elgg_view_input('select', [
	'name' => 'priority[static]',
	'value' => 1,
	'options' => range(0, 1, 0.1),
	'label' => elgg_echo('seo:sitemap:priority'),
]);

$mod .= elgg_view_input('select', [
	'name' => 'changefreq[static]',
	'value' => 'monthly',
	'options' => [
		'never',
		'yearly',
		'monthly',
		'weekly',
		'daily',
		'hourly',
		'always',
	],
	'label' => elgg_echo('seo:sitemap:changefreq'),
]);

echo elgg_view_module('aside', $title, $mod);

$dbprefix = elgg_get_config('dbprefix');
$sql = "
	SELECT *
	FROM {$dbprefix}entity_subtypes
	ORDER BY subtype
";

$rows = get_data($sql);

$options = [
	'user:' => elgg_echo('item:user'),
	'group:' => elgg_echo('item:group'),
];

foreach ($rows as $row) {
	$type = $row->type;
	$subtype = $row->subtype;
	$options["$type:$subtype"] = elgg_echo("item:$type:$subtype");
}

asort($options);

foreach ($options as $key => $label) {
	list($type, $subtype) = explode(':', $key);
	if (!$svc->getTargetUrlPattern($type, $subtype)) {
		continue;
	}

	$mod = '';

	$mod .= elgg_view_input('select', [
		'name' => "priority[$key]",
		'value' => 0.8,
		'options' => range(0, 1, 0.1),
		'label' => elgg_echo('seo:sitemap:priority'),
	]);

	$mod .= elgg_view_input('select', [
		'name' => "changefreq[$key]",
		'value' => 'daily',
		'options' => [
			'never',
			'yearly',
			'monthly',
			'weekly',
			'daily',
			'hourly',
			'always',
		],
		'label' => elgg_echo('seo:sitemap:changefreq'),
	]);

	echo elgg_view_module('info', $label, $mod);
}

echo elgg_view_input('submit', [
	'field_class' => 'elgg-foot',
	'value' => elgg_echo('seo:sitemap:generate'),
]);

<?php

use hypeJunction\Seo\RewriteService;

set_time_limit(0);

$svc = RewriteService::getInstance();

$sitemaps = elgg_get_plugin_setting('sitemaps', 'hypeSeo');
if (!$sitemaps) {
	$sitemaps = [];
} else {
	$sitemaps = unserialize($sitemaps);
}

foreach ($sitemaps as $name) {
	$file = new ElggFile();
	$file->owner_guid = elgg_get_site_entity()->guid;
	$file->setFilename("sitemaps/$name.xml");
	$file->delete();
}

$sitemap = [];

$save_sitemap = function($filename, $urls) {
	$xml = elgg_view('seo/sitemap/urlset', [
		'urls' => $urls,
	]);

	$file = new ElggFile();
	$file->owner_guid = elgg_get_site_entity()->guid;
	$file->setFilename("sitemaps/$filename");
	$file->open('write');
	$file->write($xml);
	$file->close();

	return [
		'loc' => elgg_normalize_url("seo/sitemaps/$filename"),
		'lastmod' => date('Y-m-d'),
	];
};

$priorities = get_input('priority');
$changefreq = get_input('changefreq');
$names = array_keys($priorities);

foreach ($names as $name) {
	$urls = [];
	if ($name == 'static') {
		$filename = 'static.xml';
		$static = explode(PHP_EOL, get_input('static', ''));
		foreach ($static as $s) {
			$urls[] = [
				'loc' => $s,
				'priority' => $priorities[$name],
				'changefreq' => $changefreq[$name],
			];
		}
	} else {
		list($type, $subtype) = explode(':', $name);
		$entities = new ElggBatch('elgg_get_entities', [
			'type' => $type,
			'subtype' => $subtype ?: ELGG_ENTITIES_ANY_VALUE,
			'limit' => 0,
		]);

		$index = 1;
		foreach ($entities as $entity) {
			if (elgg_is_active_plugin('hypeDiscovery')) {
				$discoverable = hypeJunction\Discovery\is_discoverable($entity);
			} else {
				$discoverable = $entity->access_id == ACCESS_PUBLIC;
			}
			if (!$discoverable) {
				continue;
			}

			$data = $svc->prepareEntityData($entity);
			if (empty($data['id']) || empty($data['sef_path'])) {
				continue;
			}

			$urls[] = [
				'loc' => elgg_normalize_url($data['sef_path']),
				'lastmod' => date('Y-m-d', max($entity->time_updated, $entity->time_created, $entity->last_action)),
				'priority' => $priorities[$name],
				'changefreq' => $changefreq[$name],
			];

			$filename = "$type$subtype$index.xml";
			if (sizeof($urls) == 50000) {
				$sitemaps[$filename] = $save_sitemap($filename, $urls);
				$urls = [];
				$index++;
			}
		}
	}

	if (!empty($urls)) {
		$sitemaps[$filename] = $save_sitemap($filename, $urls);
	}

}


$xml = elgg_view('seo/sitemap/sitemapindex', [
	'sitemaps' => $sitemaps,
]);

$file = new ElggFile();
$file->owner_guid = elgg_get_site_entity()->guid;
$file->setFilename("sitemaps/index.xml");
$file->open('write');
$file->write($xml);
$file->close();

elgg_set_plugin_setting('sitemaps', serialize(array_keys($sitemaps)), 'hypeSeo');

system_message(elgg_echo('seo:sitemap:generate:success'));
<?php
/**
 * Resource view for the /seo/{segments} named route. Replaces the
 * legacy 'seo' page handler that lived in 2.x.
 *
 * Supported segments:
 *   /seo/edit                    → inline edit dialog (delegates to resources/seo/edit)
 *   /seo/sitemaps/<filename>     → streams a sitemap XML file from the site filestore
 */

$segments = array_filter(explode('/', (string) elgg_extract('segments', $vars, '')));
$page = array_shift($segments);

switch ($page) {
	case 'edit':
		echo elgg_view_resource('seo/edit');
		return;

	case 'sitemaps':
		$filename = (string) array_shift($segments);
		if ($filename === '') {
			throw new \Elgg\Exceptions\Http\EntityNotFoundException();
		}

		$file = new ElggFile();
		$file->owner_guid = elgg_get_site_entity()->guid;
		$file->setFilename("sitemaps/{$filename}");

		if (!$file->exists()) {
			throw new \Elgg\Exceptions\Http\EntityNotFoundException();
		}

		header('Content-Type: application/xml', true);
		$file->open('read');
		echo $file->grabFile();
		$file->close();
		exit;
}

throw new \Elgg\Exceptions\Http\EntityNotFoundException();

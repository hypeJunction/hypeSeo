<?php

run_function_once('hypetraffic_upgrade_20163009a');

/**
 * Migrates SEF data from seo plugin
 */
function hypetraffic_upgrade_20163009a() {

	set_time_limit(0);

	$svc = \hypeJunction\Seo\RewriteService::getInstance();

	$site = elgg_get_site_entity();
	$dataroot = elgg_get_config('dataroot');
	$dir = new \Elgg\EntityDirLocator($site->guid);

	$paths = elgg_get_file_list($dataroot . $dir . 'seo/');
	if (empty($paths)) {
		return;
	}

	foreach ($paths as $path) {
		$basename = pathinfo($path, PATHINFO_BASENAME);

		$file = new ElggFile();
		$file->owner_guid = $site->guid;
		$file->setFilename("seo/$basename");
		$file->open('read');
		$json = $file->grabFile();
		$file->close();

		$data = json_decode($json, true);
		if (!$data) {
			$file->delete();
			continue;
		}
		
		unset($data['owner']);
		unset($data['container']);

		if (empty($data['admin_defined'])) {
			// If admin hasn't modified entity page information,
			// we will keep these as empty strings 
			// and populate them dynamically
			unset($data['title']);
			unset($data['description']);
			unset($data['keywords']);
			unset($data['metatags']);
			$data['custom'] = 'no';
		} else {
			$data['custom'] = 'yes';
		}
		
		unset($data['admin_defined']);

		if ($data['guid']) {
			$data['aliases'][] = $data['path'];
			$data['aliases'][] = $data['sef_path'];

			unset($data['path']);
			if ($data['custom'] != 'yes') {
				unset($data['sef_path']);
			}

			$entity = get_entity($data['guid']);
			if (!$entity) {
				$file->delete();
				continue;
			}

			$sef_data = $svc->prepareEntityData($entity);
			$data = array_merge($sef_data, $data);
		}
		
		if ($id = $svc->saveData($data)) {
			elgg_log("File $path migrated to DB row $id\n\n", 'WARNING');
			$file->delete();
		} else {
			elgg_log("Failed to migrate $path to DB row", 'ERROR');
		}

	}
}

<?php

namespace hypeJunction\Seo;

use ElggFile;

/**
 * @access private
 */
class Router {

	/**
	 * SEO page handler
	 * /seo/edit
	 *
	 * @param array $segments URL segments
	 * @return bool
	 */
	public static function handleSeoPages($segments) {

		$page = array_shift($segments);

		switch ($page) {
			case 'edit' :
				echo elgg_view_resource('seo/edit');
				return true;

			case 'sitemaps' :
				$filename = array_shift($segments);

				$file = new ElggFile();
				$file->owner_guid = elgg_get_site_entity()->guid;
				$file->setFilename("sitemaps/$filename");

				if (!$file->exists()) {
					return;
				}

				header('Content-Type: application/xml', true);
				
				$file->open('read');
				echo $file->grabFile();
				$file->close();
				exit;
		}

		return false;
	}


	/**
	 * Route sitemap.xml
	 *
	 * @param string $hook   "route:rewrite"
	 * @param string $type   "sitemap.xml"
	 * @param array  $return Segments and handler
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function rewriteSitemapRoute($hook, $type, $return, $params) {
		return [
			'identifier' => 'seo',
			'segments' => [
				'sitemaps',
				'index.xml',
			]
		];
	}

	/**
	 * Route SEF URLs to their original path
	 *
	 * @param string $hook   "route:rewrite"
	 * @param string $type   "all"
	 * @param array  $return Segments and handler
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function enforceRewriteRules($hook, $type, $return, $params) {

		$identifier = elgg_extract('identifier', $params);
		$segments = (array) elgg_extract('segments', $params, []);

		array_unshift($segments, $identifier);
		
		$path = implode('/', $segments);
		$url = elgg_get_site_url()  . $path;

		$svc = RewriteService::getInstance();
		$data = $svc->getRewriteRulesFromUri($url);

		if (empty($data)) {
			return;
		}
		
		$sef_path = elgg_extract('sef_path', $data);
		$original_path = elgg_extract('path', $data);
		
		if (elgg_normalize_url($sef_path) == elgg_normalize_url($original_path)) {
			return;
		}

		if (elgg_normalize_url($sef_path) !== $url && elgg_get_plugin_setting('redirect_to_canonical', 'hypeSeo')) {
			forward($sef_path);
		}

		//list($route, $guid) = explode('/', trim($original_path, '/'));

		$segments = explode('/', trim($original_path, '/'));
		$identifier = array_shift($segments);
		return [
			'identifier' => $identifier,
			'segments' => $segments,
		];
	}

}

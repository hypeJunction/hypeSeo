<?php

namespace hypeJunction\Seo;

/**
 * @access private
 */
class Router {

	/**
	 * Rewrite sitemap.xml requests onto seo/sitemaps/index.xml.
	 *
	 * @param \Elgg\Event $hook Event with the inbound segments + identifier as params
	 * @return array
	 */
	public static function rewriteSitemapRoute(\Elgg\Event $hook) {
		return [
			'identifier' => 'seo',
			'segments' => [
				'sitemaps',
				'index.xml',
			]
		];
	}

	/**
	 * Resolve SEF URLs back to the original path before route dispatch.
	 *
	 * @param \Elgg\Event $hook Event with the inbound segments + identifier as params
	 * @return array|null
	 */
	public static function enforceRewriteRules(\Elgg\Event $hook) {

		$identifier = $hook->getParam('identifier');
		$segments = (array) $hook->getParam('segments', []);

		array_unshift($segments, $identifier);
		
		$path = implode('/', $segments);
		$url = \elgg_get_site_url()  . $path;

		$svc = RewriteService::getInstance();
		$data = $svc->getRewriteRulesFromUri($url);

		if (empty($data)) {
			return;
		}
		
		$sef_path = \elgg_extract('sef_path', $data);
		$original_path = \elgg_extract('path', $data);
		
		if (\elgg_normalize_url($sef_path) == \elgg_normalize_url($original_path)) {
			return;
		}

		if (\elgg_normalize_url($sef_path) !== $url && \elgg_get_plugin_setting('redirect_to_canonical', 'hypeseo')) {
			// route:rewrite is a hook handler, not an action, so the
			// elgg_redirect_response() helper isn't usable here. Issue a
			// raw redirect and exit before the original route resolves.
			header('Location: ' . \elgg_normalize_url($sef_path), true, 302);
			exit;
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

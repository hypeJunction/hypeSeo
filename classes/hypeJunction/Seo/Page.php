<?php

namespace hypeJunction\Seo;

class Page {

	/**
	 * Setup SEO data in page head
	 *
	 * @param string $hook   "head"
	 * @param string $type   "page"
	 * @param array  $return Page head
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function setHeadMeta($hook, $type, $return, $params) {

		$svc = RewriteService::getInstance();

		$url = current_page_url();
		$data = $svc->getRewriteRulesFromUri($url);
		if (!$data) {
			return;
		}

		$data = $svc->normalizeData($data);

		$sef_path = elgg_extract('sef_path', $data);
		if (elgg_normalize_url($sef_path) != $url) {
			$return['links']['canonical'] = [
				'rel' => 'canonical',
				'href' => elgg_normalize_url($sef_path),
			];
		}

		$title = elgg_extract('title', $data);
		$description = elgg_extract('description', $data);
		$keywords = elgg_extract('keywords', $data);

		if ($title) {
			$return['title'] = $title;
		}
		if ($description) {
			$return['metas']['description'] = [
				'name' => 'description',
				'content' => $description,
			];
		}
		if ($keywords) {
			$return['metas']['keywords'] = [
				'name' => 'keywords',
				'content' => $keywords
			];
		}

		if (!empty($metatags) && is_array($metatags)) {
			foreach ($metatags as $name => $content) {
				if (!$content) {
					continue;
				}
				$name_parts = explode(':', $name);
				$namespace = array_shift($name_parts);

				$ogp = array('og', 'fb', 'article', 'profile', 'book', 'music', 'video', 'profile', 'website');
				if (in_array($namespace, $ogp)) {
					// OGP tags use 'property=""' attribute
					$return['metas'][$name] = [
						'property' => $name,
						'content' => $content,
					];
				} else {
					$return['metas'][$name] = [
						'name' => $name,
						'content' => $content,
					];
				}
			}
		}

		return $return;
	}

	/**
	 * Point robots to sitemap.xml
	 *
	 * @param string $hook   "robots.txt"
	 * @param string $type   "site"
	 * @param array  $return robots.txt
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function configureRobots($hook, $type, $return, $params) {
		$return .= PHP_EOL . "Sitemap: " . elgg_normalize_url('sitemap.xml') . PHP_EOL;
		return $return;
	}

}

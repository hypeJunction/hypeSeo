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

		$data = $svc->getRewriteRulesFromUri(current_page_url());
		if (!$data) {
			return;
		}

		$data = $svc->normalizeData($data);

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

}

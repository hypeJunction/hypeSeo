<?php

namespace hypeJunction\Seo;

/**
 * "head:page" and "robots.txt:site" event handlers.
 */
class Page {

	/**
	 * Inject canonical URL, title, and description metadata into the page head.
	 *
	 * @param \Elgg\Event $hook Event with the existing head config as value
	 * @return array|null
	 */
	public static function setHeadMeta(\Elgg\Event $hook) {
		$return = $hook->getValue();


		$svc = RewriteService::getInstance();

		$url = elgg_get_current_url();
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

				$ogp = ['og', 'fb', 'article', 'profile', 'book', 'music', 'video', 'profile', 'website'];
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
	 * Append a Sitemap directive to robots.txt output.
	 *
	 * @param \Elgg\Event $hook Event with the existing robots.txt body as value
	 * @return string
	 */
	public static function configureRobots(\Elgg\Event $hook) {
		$return = $hook->getValue();

		$return .= PHP_EOL . 'Sitemap: ' . elgg_normalize_url('sitemap.xml') . PHP_EOL;
		return $return;
	}
}

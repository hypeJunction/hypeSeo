<?php

namespace hypeJunction\Seo;

class RelFollow {

	/**
	 * Strip rel="nofollow" for content posted by trusted users
	 *
	 * @param string $hook   "view"
	 * @param string $type   "object/<subtype>"
	 * @param string $return View
	 * @param array  $params Hook params
	 *
	 * @return string
	 */
	public static function trustLinksInContent($hook, $type, $return, $params) {

		$vars = elgg_extract('vars', $params);
		$entity = elgg_extract('entity', $vars);

		if (!$entity instanceof \ElggEntity) {
			return null;
		}

		$owner = $entity->getOwnerEntity();
		if (!$owner) {
			return null;
		}

		if (!self::isTrusted($owner)) {
			return null;
		}

		return self::stripRel($return);
	}

	/**
	 * Check if user is trusted
	 *
	 * @param \ElggEntity $owner Owner
	 *
	 * @return bool
	 */
	public static function isTrusted(\ElggEntity $owner) {
		if (!$owner instanceof \ElggUser) {
			return false;
		}

		if ($owner->isAdmin()) {
			return true;
		}

		if (elgg_is_active_plugin('trusted_users')) {
			return trusted_users_is_trusted($owner);
		}

		return false;
	}

	/**
	 * Strip rel="nofollow"
	 *
	 * @param string $html HTML
	 *
	 * @return string
	 */
	public static function stripRel($html) {
		$pattern = '(\<a.*?)(rel=\"nofollow\")(.*?\>)';

		return preg_replace("/$pattern/im", "$1$3", $html);
	}
}
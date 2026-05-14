<?php

namespace hypeJunction\Seo;

/**
 * "view:object/*" event handler that strips rel="nofollow" for trusted content.
 */
class RelFollow {

	/**
	 * Strip rel="nofollow" for content posted by trusted users.
	 *
	 * @param \Elgg\Event $hook Event with the rendered HTML as value and the entity in vars
	 * @return string|null
	 */
	public static function trustLinksInContent(\Elgg\Event $hook) {
		if (!str_starts_with($hook->getType(), 'object/')) {
			return null;
		}

		$vars = $hook->getParam('vars', []);
		$entity = elgg_extract('entity', (array) $vars);

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

		return self::stripRel($hook->getValue());
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

		return preg_replace("/$pattern/im", '$1$3', $html);
	}
}

<?php

namespace hypeJunction\Seo;

use ElggMenuItem;

/**
 * "register, menu:extras" event handlers.
 */
class Menus {

	/**
	 * Append the SEO edit shortcut to the extras menu for admins.
	 *
	 * @param \Elgg\Event $hook Event with the existing menu items as value
	 * @return ElggMenuItem[]|null
	 */
	public static function setupExtrasMenu(\Elgg\Event $hook) {
		$return = $hook->getValue();


		if (!elgg_is_admin_logged_in()) {
			return;
		}

		$return[] = ElggMenuItem::factory([
			'name' => 'seo',
			'text' => elgg_view_icon('search'),
			'title' => elgg_echo('seo:edit'),
			'href' => elgg_http_add_url_query_elements('seo/edit', [
				'page_uri' => elgg_get_current_url(),
			]),
			'link_class' => 'elgg-lightbox',
			'data-colorbox-opts' => json_encode([
				'maxWidth' => '600px',
			]),
		]);

		return $return;
	}
}

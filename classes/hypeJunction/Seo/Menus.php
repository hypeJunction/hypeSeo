<?php

namespace hypeJunction\Seo;

use ElggMenuItem;

class Menus {

	/**
	 * Setup page menu
	 *
	 * @param string         $hook   "register"
	 * @param string         $type   "menu:extras"
	 * @param ElggMenuItem[] $return Menu
	 * @param array          $params Hook params
	 * @return ElggMenuItem[]
	 */
	public static function setupExtrasMenu($hook, $type, $return, $params) {

		if (!elgg_is_admin_logged_in()) {
			return;
		}

		$return[] = ElggMenuItem::factory([
			'name' => 'seo',
			'text' => elgg_view_icon('search'),
			'title' => elgg_echo('seo:edit'),
			'href' => elgg_http_add_url_query_elements('seo/edit', array(
				'page_uri' => current_page_url(),
			)),
			'link_class' => 'elgg-lightbox',
			'data-colorbox-opts' => json_encode([
				'maxWidth' => '600px',
			]),
		]);

		return $return;
	}

}

<?php

namespace hypeJunction\Multibyte;

class Menus {

	public static function setupControlPanelMenu($hook, $type, $return, $params) {
		if (elgg_get_plugin_setting('utf8mb4-upgrade', 'elgg-mb4')) {
			return;
		}

		$return[] = \ElggMenuItem::factory([
			'name' => 'mb4',
			'text' => 'Multibyte Upgrade',
			'href' => 'admin/multibyte',
			'class' => 'elgg-button elgg-button-action',
		]);

		return $return;
	}
}
<?php

require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', function() {
	elgg_register_plugin_hook_handler('register', 'menu:admin_control_panel', [\hypeJunction\Multibyte\Menus::class, 'setupControlPanelMenu']);

	elgg_register_action('multibyte/automatic', __DIR__ . '/actions/multibyte/automatic.php', 'admin');
	elgg_register_action('multibyte/manual', __DIR__ . '/actions/multibyte/manual.php', 'admin');
});

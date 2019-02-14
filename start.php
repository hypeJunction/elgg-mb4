<?php

require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('upgrade', 'system', function() {
	if (!elgg_get_plugin_setting('utf8mb4', 'elgg-mb4')) {
		$success = false;

		try {
			$upgrade = new \Elgg\Upgrades\AlterDatabaseToMultiByteCharset();
			$upgrade->run();

			$success = true;
		} catch (Exception $ex) {
			elgg_log($ex->getMessage(), 'ERROR');
			register_error($ex->getMessage());
		}

		if ($success || get_input('force_mb4')) {
			elgg_set_plugin_setting('utf8mb4', time(), 'elgg-mb4');
		}
	}
});
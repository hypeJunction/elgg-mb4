<?php

set_time_limit(0);

try {
	$script = new \Elgg\Upgrades\AlterDatabaseToMultiByteCharset();
	$script->run();
} catch (Exception $ex) {
	elgg_log($ex->getMessage(), 'ERROR');

	return elgg_error_response($ex->getMessage());
}

elgg_set_plugin_setting('utf8mb4-upgrade', time(), 'elgg-mb4');

return elgg_ok_response('Database has been upgraded successfully');
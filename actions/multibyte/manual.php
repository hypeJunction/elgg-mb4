<?php

elgg_set_plugin_setting('utf8mb4-upgrade', time(), 'elgg-mb4');

return elgg_ok_response('Upgrade marked as complete');
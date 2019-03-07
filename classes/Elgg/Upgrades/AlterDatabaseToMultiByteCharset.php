<?php

namespace Elgg\Upgrades;

/**
 * Updates database charset to utf8mb4
 */
class AlterDatabaseToMultiByteCharset {

	private $utf8mb4_tables = [
		// InnoDB
		'access_collection_membership',
		'access_collections',
		'annotations',
		'api_users',
		'config',
		'entities',
		'entity_relationships',
		'metadata',
		'private_settings',
		'queue',
		'river',
		'system_log',
		'users_remember_me_cookies',
		//'users_sessions',
		'sites_entity',
		'users_entity',
		'objects_entity',
		'groups_entity',
		// MEMORY
		'hmac_cache',
		'users_apisessions',
	];

	// Columns with utf8 encoding and utf8_general_ci collation
	// $table => [
	//   $column => $index
	// ]

	private $non_mb4_columns = [
		'config' => [
			'name' => [
				'primary' => true,
				'name' => 'name',
				'unique' => false,
			],
		],
		'entities' => [
			'subtype' => [
				'primary' => false,
				'name' => 'subtype',
				'unique' => false,
			],
		],
		'queue' => [
			'name' => [
				'primary' => false,
				'name' => "name",
				'unique' => false,
			],
		],
		'users_sessions' => [
			'session' => [
				'primary' => true,
				'name' => 'session',
				'unique' => false,
			],
		],
		'hmac_cache' => [
			'hmac' => [
				'primary' => true,
				'name' => 'hmac',
				'unique' => false,
			],
		],
		'system_log' => [
			'object_class' => [
				'primary' => false,
				'name' => 'object_class',
				'unique' => false,
			],
			'object_type' => [
				'primary' => false,
				'name' => 'object_type',
				'unique' => false,
			],
			'object_subtype' => [
				'primary' => false,
				'name' => 'object_subtype',
				'unique' => false,
			],
			'event' => [
				'primary' => false,
				'name' => 'event',
				'unique' => false,
			],
			'river_key' => [
				'primary' => false,
				'name' => 'river_key',
				'unique' => false,
				'columns' => ['object_type', 'object_subtype', 'event']
			],
		]
	];

	/**
	 * {@inheritdoc}
	 */
	public function run() {

		$config = [
			'prefix' => elgg_get_config('dbprefix'),
			'database' => elgg_get_config('dbname'),
		];

		// required to allow bigger index sizes required for utf8mb4
		_elgg_services()->db->updateData("SET GLOBAL innodb_large_prefix = 'ON'");

		_elgg_services()->db->updateData("
				ALTER DATABASE
    			`{$config['database']}`
    			CHARACTER SET = utf8mb4
    			COLLATE = utf8mb4_unicode_ci
			");

		foreach ($this->utf8mb4_tables as $table) {
			if (!empty($this->non_mb4_columns[$table])) {
				foreach ($this->non_mb4_columns[$table] as $column => $index) {
					if ($index) {
						if ($index['primary']) {
							_elgg_services()->db->updateData("
									ALTER TABLE {$config['prefix']}{$table}
									DROP PRIMARY KEY
								");
						} else {
							_elgg_services()->db->updateData("
									ALTER TABLE {$config['prefix']}{$table}
									DROP KEY {$index['name']}
								");
						}
					}
				}
			}

			_elgg_services()->db->updateData("
					ALTER TABLE {$config['prefix']}{$table}
					ENGINE = InnoDB
					ROW_FORMAT=DYNAMIC
				");

			_elgg_services()->db->updateData("
					ALTER TABLE {$config['prefix']}{$table}
					CONVERT TO CHARACTER SET utf8mb4
					COLLATE utf8mb4_general_ci
				");

			if (!empty($this->non_mb4_columns[$table])) {
				foreach ($this->non_mb4_columns[$table] as $column => $index) {
					if (empty($index['columns'])) {
						// Alter table only if the key is not composite
						_elgg_services()->db->updateData("
								ALTER TABLE {$config['prefix']}{$table}
								MODIFY $column VARCHAR(255)
								CHARACTER SET utf8
								COLLATE utf8_unicode_ci
							");
					}

					if (!$index) {
						continue;
					}

					$sql = "ADD";
					if ($index['unique']) {
						$sql .= " UNIQUE ({$index['name']})";
					} else if ($index['primary']) {
						$sql .= " PRIMARY KEY ({$index['name']})";
					} else {
						$key_columns = elgg_extract('columns', $index, [$column]);
						$key_columns = implode(',', $key_columns);
						$sql .= " KEY {$index['name']} ($key_columns)";
					}

					_elgg_services()->db->updateData("
							ALTER TABLE {$config['prefix']}{$table}
							$sql
						");
				}
			}
		}

	}

}

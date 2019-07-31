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
		'datalists',
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
		'entity_subtypes',
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
		'datalists' => [
			'name' => [
				'primary' => true,
				'name' => 'name',
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
	];

	/**
	 * {@inheritdoc}
	 */
	public function run() {
		$queries = $this->generateQueries();

		foreach ($queries as $query) {
			elgg_log('Multibyte upgrade! Executing query: ' . $query, 'WARNING');

			_elgg_services()->db->updateData($query);
		}
	}

	public function generateQueries() {
		$config = [
			'prefix' => elgg_get_config('dbprefix'),
			'database' => elgg_get_config('dbname'),
		];

		$queries = [];

		// required to allow bigger index sizes required for utf8mb4
		//$queries[] = "SET GLOBAL innodb_large_prefix = 'ON'";

		$queries[] = "
			ALTER DATABASE
    		`{$config['database']}`
    		CHARACTER SET = utf8mb4
    		COLLATE = utf8mb4_unicode_ci
		";

		foreach ($this->utf8mb4_tables as $table) {
			if (!empty($this->non_mb4_columns[$table])) {
				foreach ($this->non_mb4_columns[$table] as $column => $index) {
					if ($index) {
						if ($index['primary']) {
							$queries[] = "
								ALTER TABLE `{$config['database']}`.`{$config['prefix']}{$table}`
								DROP PRIMARY KEY
							";
						} else {
							$queries[] = "
								ALTER TABLE `{$config['database']}`.`{$config['prefix']}{$table}`
								DROP KEY `{$index['name']}`
							";
						}
					}
				}
			}

			$queries[] = "
				ALTER TABLE `{$config['database']}`.`{$config['prefix']}{$table}`
				ENGINE = InnoDB
				ROW_FORMAT=DYNAMIC
			";

			$queries[] = "
				ALTER TABLE `{$config['database']}`.`{$config['prefix']}{$table}`
				CONVERT TO CHARACTER SET utf8mb4
				COLLATE utf8mb4_general_ci
			";

			if (!empty($this->non_mb4_columns[$table])) {
				foreach ($this->non_mb4_columns[$table] as $column => $index) {
					if (empty($index['columns'])) {
						// Alter table only if the key is not composite
						$queries[] = "
							ALTER TABLE `{$config['database']}`.`{$config['prefix']}{$table}`
							MODIFY `$column` VARCHAR(255)
							CHARACTER SET utf8
							COLLATE utf8_unicode_ci
						";
					}

					if (!$index) {
						continue;
					}

					$sql = "ADD";
					if ($index['unique']) {
						$sql .= " UNIQUE (`{$index['name']}`)";
					} else if ($index['primary']) {
						$sql .= " PRIMARY KEY (`{$index['name']}`)";
					} else {
						$key_columns = elgg_extract('columns', $index, [$column]);
						$key_columns = array_map(function ($e) {
							return "`$e`";
						}, $key_columns);
						$key_columns = implode(',', $key_columns);
						$sql .= " KEY `{$index['name']}` ($key_columns)";
					}

					$queries[] = "
						ALTER TABLE `{$config['database']}`.`{$config['prefix']}{$table}`
						$sql
					";
				}
			}

			$queries[] = "REPAIR TABLE `{$config['database']}`.`{$config['prefix']}{$table}`";
			$queries[] = "OPTIMIZE TABLE `{$config['database']}`.`{$config['prefix']}{$table}`";
		}

		return array_map(function ($q) {
			return preg_replace('/(\r\n|\n|\s{2,}|\t)/', ' ', $q) . ';';
		}, $queries);
	}

}

<?php
/**
 * Handling the custom table for storing Restricted Domains information.
 *
 * @package DarkMatterPlugin\DomainMapping\Data
 */

namespace DarkMatter\DomainMapping\Data;

use DarkMatter\Helper\CustomTable;

/**
 * Class RestrictedDomain
 */
class RestrictedDomain extends CustomTable {
	/**
	 * Columns definition for the Restricted Domain table.
	 *
	 * @return array
	 */
	public function get_columns() {
		return [
			'id'         => [
				'autoincrement' => true,
				'nullable'      => false,
				'queryable'     => false,
				'type'          => 'BIGINT',
				'type_storage'  => 20,
			],
			'domain'     => [
				'nullable'      => false,
				'queryable'     => false,
				'type'          => 'VARCHAR',
				'type_storage'  => 255,
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function get_indexes() {
		return false;
	}

	/**
	 * Restricted Domains primary key.
	 *
	 * @return string
	 */
	public function get_primary_key() {
		return 'id';
	}

	/**
	 * Restricted Domains table name.
	 *
	 * @return string
	 */
	public function get_tablename() {
		global $wpdb;
		return $wpdb->base_prefix . 'domain_restrict';
	}
}

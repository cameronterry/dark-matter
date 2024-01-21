<?php
/**
 * Handling the custom table which stores the domain mapping information.
 *
 * @package DarkMatterPlugin\DomainMapping\Data
 */

namespace DarkMatter\DomainMapping\Data;

use DarkMatter\Helper\CustomTable;

/**
 * Class DomainMapping
 */
class DomainMapping extends CustomTable {
	/**
	 * Columns for the Domain Mapping table.
	 *
	 * @return array
	 */
	protected function get_columns() {
		return [
			'id'         => [
				'autoincrement' => true,
				'nullable'      => false,
				'queryable'     => false,
				'type'          => 'BIGINT',
				'type_storage'  => 20,
			],
			'blog_id'    => [
				'nullable'      => false,
				'queryable'     => false,
				'type'          => 'BIGINT',
				'type_storage'  => 20,
			],
			'is_primary' => [
				'default'       => '0',
				'nullable'      => true,
				'queryable'     => false,
				'type'          => 'TINYINT',
				'type_storage'  => 4,
			],
			'domain'     => [
				'nullable'      => false,
				'queryable'     => false,
				'type'          => 'VARCHAR',
				'type_storage'  => 255,
			],
			'active'     => [
				'default'       => '1',
				'nullable'      => false,
				'queryable'     => false,
				'type'          => 'TINYINT',
				'type_storage'  => 4,
			],
			'is_https'   => [
				'default'       => '0',
				'nullable'      => true,
				'queryable'     => false,
				'type'          => 'TINYINT',
				'type_storage'  => 4,
			],
			'type'       => [
				'default'       => '1',
				'nullable'      => true,
				'queryable'     => false,
				'type'          => 'TINYINT',
				'type_storage'  => 4,
			],
		];
	}

	/**
	 * Not used currently.
	 *
	 * @return false
	 */
	protected function get_indexes() {
		return false;
	}

	/**
	 * Column for the primary key.
	 *
	 * @return string
	 */
	protected function get_primary_key() {
		return 'id';
	}

	/**
	 * Return the domain mapping table name.
	 *
	 * @return string
	 */
	protected function get_tablename() {
		global $wpdb;
		return $wpdb->base_prefix . 'domain_mapping';
	}
}

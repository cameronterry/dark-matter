<?php
/**
 * Handling the custom table for storing Restricted Domains information.
 *
 * @package DarkMatterPlugin\DomainMapping\Data
 */

namespace DarkMatter\DomainMapping\Data;

use DarkMatter\DomainMapping\Helper;
use DarkMatter\Helper\CustomTable;

/**
 * Class RestrictedDomain
 */
class RestrictedDomain extends CustomTable {

	/**
	 * Add a restricted domain to the database.
	 *
	 * @param array $data Data to be used to create the restricted domain.
	 * @return \WP_Error|string
	 */
	public function add( $data = [] ) {
		$data = $this->parse_args( $data, false );
		if ( false === $data ) {
			return false;
		}

		$domain = Helper::instance()->check_domain( $data['domain'] );
		if ( is_wp_error( $domain ) ) {
			return $domain;
		}

		$query     = new RestrictedDomainQuery();
		$domain_id = $query->get_id_by_domain( $data['domain'] );
		if ( $domain_id ) {
			return new \WP_Error(
				'restricted_domain_already_exists',
				sprintf(
					/* translators: %s: restricted domain. */
					__( 'Domain is already restricted: %s.', 'dark-matter' ),
					$data['domain']
				)
			);
		}

		$result = parent::add(
			[
				'domain' => $domain,
			]
		);
		if ( $result ) {
			/**
			 * Fires when a domain is added to the restricted list.
			 *
			 * @since 2.0.0
			 *
			 * @param string $domain Domain name that was restricted.
			 */
			do_action( 'darkmatter_restrict_add', $domain );
		}

		return $domain;
	}

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
				'queryable'     => true,
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

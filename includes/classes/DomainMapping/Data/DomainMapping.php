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
	 * Check to ensure the string domain passes some basic checks.
	 *
	 * @param string $domain Domain to check.
	 * @return \WP_Error|null
	 */
	private function check( $domain ) {
		if ( empty( $domain ) ) {
			return new \WP_Error( 'empty', __( 'Please include a fully qualified domain name to be added.', 'dark-matter' ) );
		}

		/**
		 * Ensure that the URL is purely a domain. In order for the parse_url() to work, the domain must be prefixed
		 * with a double forward slash.
		 */
		if ( false === stripos( $domain, '//' ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			$domain_parts = parse_url( '//' . ltrim( $domain, '/' ) );
		} else {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			$domain_parts = parse_url( $domain );
		}

		if ( ! empty( $domain_parts['path'] ) || ! empty( $domain_parts['port'] ) || ! empty( $domain_parts['query'] ) ) {
			return new \WP_Error( 'unsure', __( 'The domain provided contains path, port, or query string information. Please removed this before continuing.', 'dark-matter' ) );
		}

		$domain = $domain_parts['host'];

		/**
		 * Check to ensure we have a valid domain to work with.
		 */
		if ( ! filter_var( $domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME ) ) {
			return new \WP_Error( 'domain', __( 'The domain is not valid.', 'dark-matter' ) );
		}

		if ( defined( 'DOMAIN_CURRENT_SITE' ) && DOMAIN_CURRENT_SITE === $domain ) {
			return new \WP_Error( 'wp-config', __( 'You cannot configure the WordPress Network primary domain.', 'dark-matter' ) );
		}

		if ( is_main_site() ) {
			return new \WP_Error( 'root', __( 'Domains cannot be mapped to the main / root Site.', 'dark-matter' ) );
		}

		$reserve = \DarkMatter\DomainMapping\Manager\Restricted::instance();
		if ( $reserve->is_exist( $domain ) ) {
			return new \WP_Error( 'reserved', __( 'This domain has been reserved.', 'dark-matter' ) );
		}

		/**
		 * Allow for additional checks beyond the in-built Dark Matter ones.
		 *
		 * Ensure that in the case of an error, it should return a WP_Error
		 * object. This is used when providing error messages to the CLI and
		 * REST API endpoints.
		 *
		 * @since 2.0.0
		 *
		 * @param string $domain Fully qualified domain name.
		 * @return string
		 */
		return apply_filters( 'darkmatter_domain_basic_check', $domain );
	}

	/**
	 * Check the domain type to ensure it is supported/valid.
	 *
	 * @param string $type Domain type value to check.
	 * @return true|\WP_Error
	 */
	private function check_type( $type ) {
		/**
		 * Type is either "main" or "media".
		 */
		$types = [
			DM_DOMAIN_TYPE_MAIN,
			DM_DOMAIN_TYPE_MEDIA,
		];

		if ( ! in_array( $type, $types, true ) ) {
			return new \WP_Error( 'type', __( 'The type for the new domain is not supported.', 'dark-matter' ) );
		}

		return true;
	}

	/**
	 * Columns for the Domain Mapping table.
	 *
	 * @return array
	 */
	public function get_columns() {
		return [
			'id'         => [
				'autoincrement' => true,
				'nullable'      => false,
				'queryable'     => true,
				'queryable_in'  => true,
				'type'          => 'BIGINT',
				'type_storage'  => 20,
			],
			'blog_id'    => [
				'nullable'      => false,
				'queryable'     => true,
				'queryable_in'  => true,
				'type'          => 'BIGINT',
				'type_storage'  => 20,
			],
			'is_primary' => [
				'default'       => '0',
				'nullable'      => true,
				'queryable'     => true,
				'type'          => 'TINYINT',
				'type_storage'  => 4,
			],
			'domain'     => [
				'nullable'      => false,
				'queryable'     => true,
				'queryable_in'  => true,
				'type'          => 'VARCHAR',
				'type_storage'  => 255,
			],
			'active'     => [
				'default'       => '1',
				'nullable'      => false,
				'queryable'     => true,
				'type'          => 'TINYINT',
				'type_storage'  => 4,
			],
			'is_https'   => [
				'default'       => '0',
				'nullable'      => true,
				'queryable'     => true,
				'type'          => 'TINYINT',
				'type_storage'  => 4,
			],
			'type'       => [
				'default'       => '1',
				'nullable'      => true,
				'queryable'     => true,
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
	public function get_primary_key() {
		return 'id';
	}

	/**
	 * Return the domain mapping table name.
	 *
	 * @return string
	 */
	public function get_tablename() {
		global $wpdb;
		return $wpdb->base_prefix . 'domain_mapping';
	}
}

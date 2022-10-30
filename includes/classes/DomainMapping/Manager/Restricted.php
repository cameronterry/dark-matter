<?php
/**
 * Manager for Restricted domains.
 *
 * @since 2.0.0
 *
 * @package DarkMatter
 */

namespace DarkMatter\DomainMapping\Manager;

/**
 * Class Restricted
 *
 * Previously called `DarkMatter_Restrict`.
 *
 * @since 2.0.0
 */
class Restricted {
	/**
	 * Restrict table name for database operations.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	private $restrict_table = '';

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->restrict_table = $wpdb->base_prefix . 'domain_restrict';
	}

	/**
	 * Perform basic checks before committing to a action performed by a method.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $fqdn Fully qualified domain name.
	 * @return \WP_Error|boolean       True on pass. WP_Error on failure.
	 */
	private function _basic_checks( $fqdn ) {
		if ( empty( $fqdn ) ) {
			return new \WP_Error( 'empty', __( 'Please include a fully qualified domain name to be added.', 'dark-matter' ) );
		}

		/**
		 * Ensure that the URL is purely a domain. In order for the parse_url() to work, the domain must be prefixed
		 * with a double forward slash.
		 */
		if ( false === stripos( $fqdn, '//' ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			$domain_parts = parse_url( '//' . ltrim( $fqdn, '/' ) );
		} else {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			$domain_parts = parse_url( $fqdn );
		}

		if ( ! empty( $domain_parts['path'] ) || ! empty( $domain_parts['port'] ) || ! empty( $domain_parts['query'] ) ) {
			return new \WP_Error( 'unsure', __( 'The domain provided contains path, port, or query string information. Please removed this before continuing.', 'dark-matter' ) );
		}

		$fqdn = $domain_parts['host'];

		if ( defined( 'DOMAIN_CURRENT_SITE' ) && DOMAIN_CURRENT_SITE === $fqdn ) {
			return new \WP_Error( 'wp-config', __( 'You cannot configure the WordPress Network primary domain.', 'dark-matter' ) );
		}

		$domains = \DarkMatter_Domains::instance();
		if ( $domains->is_exist( $fqdn ) ) {
			return new \WP_Error( 'used', __( 'This domain is in use.', 'dark-matter' ) );
		}

		return $fqdn;
	}

	/**
	 * Add a domain to the Restrict list.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $fqdn Domain to be added to the reserve list.
	 * @return \WP_Error|boolean       True on success, WP_Error otherwise.
	 */
	public function add( $fqdn = '' ) {
		$fqdn = $this->_basic_checks( $fqdn );

		if ( is_wp_error( $fqdn ) ) {
			return $fqdn;
		}

		if ( $this->is_exist( $fqdn ) ) {
			return new \WP_Error( 'exists', __( 'The Domain is already Restricted.', 'dark-matter' ) );
		}

		/**
		 * Add the domain to the database.
		 */
		global $wpdb;

		// phpcs:ignore
		$result = $wpdb->insert(
			$this->restrict_table,
			array(
				'domain' => $fqdn,
			),
			array( '%s' )
		);

		if ( ! $result ) {
			return new \WP_Error( 'unknown', __( 'An unknown error has occurred. The domain has not been removed from the Restrict list.', 'dark-matter' ) );
		}

		$this->refresh_cache();

		/**
		 * Fires when a domain is added to the restricted list.
		 *
		 * @since 2.0.0
		 *
		 * @param string $fqdn Domain name that was restricted.
		 */
		do_action( 'darkmatter_restrict_add', $fqdn );

		return true;
	}

	/**
	 * Delete a domain to the Restrict list.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $fqdn Domain to be deleted to the restrict list.
	 * @return \WP_Error|boolean       True on success, WP_Error otherwise.
	 */
	public function delete( $fqdn = '' ) {
		$fqdn = $this->_basic_checks( $fqdn );

		if ( is_wp_error( $fqdn ) ) {
			return $fqdn;
		}

		if ( ! $this->is_exist( $fqdn ) ) {
			return new \WP_Error( 'missing', __( 'The Domain is not found in the Restrict list.', 'dark-matter' ) );
		}

		/**
		 * Remove the domain to the database.
		 */
		global $wpdb;

		// phpcs:ignore
		$result = $wpdb->delete(
			$this->restrict_table,
			array(
				'domain' => $fqdn,
			),
			array( '%s' )
		);

		if ( ! $result ) {
			return new \WP_Error( 'unknown', __( 'An unknown error has occurred. The domain has not been removed from the Restrict list.', 'dark-matter' ) );
		}

		$this->refresh_cache();

		/**
		 * Fires when a domain is deleted from the restricted list.
		 *
		 * @since 2.0.0
		 *
		 * @param string $fqdn Domain name that was restricted.
		 */
		do_action( 'darkmatter_restrict_delete', $fqdn );

		return true;
	}

	/**
	 * Retrieve all restrict domains.
	 *
	 * @since 2.0.0
	 *
	 * @return array List of restrict domains.
	 */
	public function get() {
		/**
		 * Attempt to retreive the domain from cache.
		 */
		$restrict_domains = wp_cache_get( 'restricted', 'dark-matter' );

		/**
		 * Fires after the domains have been retrieved from cache (if available)
		 * and before the database is used to retrieve the Restricted domains.
		 *
		 * @since 2.0.0
		 *
		 * @param array $restricted_domains Restricted domains retrieved from Object Cache.
		 */
		$restrict_domains = apply_filters( 'darkmatter_restricted_get', $restrict_domains );

		if ( $restrict_domains ) {
			return $restrict_domains;
		}

		/**
		 * Then attempt to retrieve the domains from the database, assuming
		 * there is any.
		 */
		global $wpdb;

		// phpcs:ignore
		$restricted_domains = $wpdb->get_col( "SELECT domain FROM {$this->restrict_table} ORDER BY domain" );

		if ( empty( $restricted_domains ) ) {
			$restricted_domains = array();
		}

		/**
		 * May seem peculiar to cache an empty array here but as this will
		 * likely be a slow changing data set, then it's pointless to keep
		 * pounding the database unnecessarily.
		 */
		wp_cache_add( 'restricted', $restricted_domains, 'dark-matter' );

		return $restricted_domains;
	}

	/**
	 * Check if a domain has been restricted.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $fqdn Domain to check.
	 * @return boolean       True if found. False otherwise.
	 */
	public function is_exist( $fqdn = '' ) {
		if ( empty( $fqdn ) ) {
			return false;
		}

		$restricted_domains = $this->get();

		return in_array( $fqdn, $restricted_domains );
	}

	/**
	 * Helper method to refresh the cache for Restricted domains.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function refresh_cache() {
		wp_cache_delete( 'restricted', 'dark-matter' );
		$this->get();
	}

	/**
	 * Return the Singleton Instance of the class.
	 *
	 * @since 2.0.0
	 *
	 * @return Restricted
	 */
	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}

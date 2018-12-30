<?php

defined( 'ABSPATH' ) || die;

class DarkMatter_Reserve {
    /**
     * Reserve table name for database operations.
     *
     * @var string
     */
    private $reserve_table = '';

    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb;
        $this->reserve_table = $wpdb->base_prefix . 'domain_reserve';
    }

    /**
     * Perform basic checks before committing to a action performed by a method.
     *
     * @param  string           $fqdn Fully qualified domain name.
     * @return WP_Error|boolean       True on pass. WP_Error on failure.
     */
    private function _basic_checks( $fqdn ) {
        if ( empty( $fqdn ) ) {
            return new WP_Error( 'empty', __( 'Please include a fully qualified domain name to be added.', 'dark-matter' ) );
        }

        /**
         * Ensure that the URL is purely a domian. In order for the parse_url()
         * to work, the domain must be prefixed with a double forward slash.
         */
        if ( false === stripos( $fqdn, '//' ) ) {
            $domain_parts = parse_url( '//' . ltrim( $fqdn, '/' ) );
        } else {
            $domain_parts = parse_url( $fqdn );
        }

        if ( empty( $domain_parts['path'] ) || empty( $domain_parts['port'] ) || empty( $domain_parts['query'] ) ) {
            return new WP_Error( 'unsure', __( 'The domain provided contains path, port, or query string information. Please removed this before continuing.', 'dark-matter' ) );
        }

        $fqdn = $domain_parts['host'];

        if ( defined( 'DOMAIN_CURRENT_SITE' ) && DOMAIN_CURRENT_SITE === $fqdn ) {
            return new WP_Error( 'wp-config', __( 'You cannot configure the WordPress Network primary domain.', 'dark-matter' ) );
        }

        $domains = DarkMatter_Domains::instance();
        if ( $domains->is_exist( $fqdn ) ) {
            return new WP_Error( 'used', __( 'This domain is in use.', 'dark-matter' ) );
        }

        return $fqdn;
    }

    /**
     * Add a domain to the Reserve list.
     *
     * @param  string           $fqdn Domain to be added to the reserve list.
     * @return WP_Error|boolean       True on success, WP_Error otherwise.
     */
    public function add( $fqdn = '' ) {
        $fqdn = $this->_basic_checks( $fqdn );

        if ( is_wp_error( $fqdn ) ) {
            return $fqdn;
        }

        if ( $this->is_exist( $fqdn ) ) {
            return new WP_Error( 'exists', __( 'The Domain is already Reserved.', 'dark-matter' ) );
        }

        /**
         * Add the domain to the database.
         */
        global $wpdb;
        $result = $wpdb->insert( $this->reserve_table, array(
            'domain' => $fqdn,
        ), array( '%s' ) );

        if ( ! $result ) {
            return new WP_Error( 'unknown', __( 'An unknown error has occurred. The domain has not been removed from the Reserved list.', 'dark-matter' ) );
        }

        $this->refresh_cache();

        return true;
    }

    /**
     * Delete a domain to the Reserve list.
     *
     * @param  string           $fqdn Domain to be deleted to the reserve list.
     * @return WP_Error|boolean       True on success, WP_Error otherwise.
     */
    public function delete( $fqdn = '' ) {
        $fqdn = $this->_basic_checks( $fqdn );

        if ( is_wp_error( $fqdn ) ) {
            return $fqdn;
        }

        if ( ! $this->is_exist( $fqdn ) ) {
            return new WP_Error( 'missing', __( 'The Domain is not found in the Reserved list.', 'dark-matter' ) );
        }

        /**
         * Remove the domain to the database.
         */
        global $wpdb;
        $result = $wpdb->delete( $this->reserve_table, array(
            'domain' => $fqdn
        ), array( '%s' ) );

        if ( ! $result ) {
            return new WP_Error( 'unknown', __( 'An unknown error has occurred. The domain has not been removed from the Reserved list.', 'dark-matter' ) );
        }

        $this->refresh_cache();

        return true;
    }

    /**
     * Retrieve all reserve domains.
     *
     * @return array List of reserve domains.
     */
    public function get() {
        /**
         * Attempt to retreive the domain from cache.
         */
        $reserve_domains = wp_cache_get( 'reserved', 'dark-matter' );

        if ( $reserve_domains ) {
            return $reserve_domains;
        }

        /**
         * Then attempt to retrieve the domains from the database, assuming
         * there is any.
         */
        global $wpdb;
        $reserve_domains = $wpdb->get_col( "SELECT domain FROM {$this->reserve_table} ORDER BY domain" );

        if ( empty( $reserve_domains ) ) {
            $reserve_domains = array();
        }

        /**
         * May seem peculiar to cache an empty array here but as this will
         * likely be a slow changing data set, then it's pointless to keep
         * pounding the database unnecessarily.
         */
        wp_cache_add( 'reserved', $reserve_domains, 'dark-matter' );

        return $reserve_domains;
    }

    /**
     * Check if a domain has been reserved.
     *
     * @param  string  $fqdn Domain to check.
     * @return boolean       True if found. False otherwise.
     */
    public function is_exist( $fqdn = '' ) {
        if ( empty( $fqdn ) ) {
            return false;
        }

        $reserve_domains = $this->get();

        return in_array( $fqdn, $reserve_domains );
    }

    /**
     * Helper method to refresh the cache for Reserved domains.
     *
     * @return void
     */
    public function refresh_cache() {
        wp_cache_delete( 'reserved', 'dark-matter' );
        $this->get();
    }

    /**
     * Return the Singleton Instance of the class.
     *
     * @return void
     */
    public static function instance() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }
}
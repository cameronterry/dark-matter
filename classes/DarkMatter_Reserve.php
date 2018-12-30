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
     * Add a domain to the Reserve list.
     *
     * @param  string           $fqdn Domain to be added to the reserve list.
     * @return WP_Error|boolean       True on success, WP_Error otherwise.
     */
    public function add( $fqdn = '' ) {
        if ( $this->is_exists( $fqdn ) ) {
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
        if ( ! $this->is_exists( $fqdn ) ) {
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
    public function is_exists( $fqdn = '' ) {
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
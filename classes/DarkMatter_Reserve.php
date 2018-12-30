<?php

defined( 'ABSPATH' ) || die;

class DarkMatter_Reserve {
    /**
     * Constructor.
     */
    public function __construct() {
        $this->reserve_table = $wpdb->base_prefix . 'domain_reserve';
    }

    /**
     * Add a domain to the Reserve list.
     *
     * @param  string           $fqdn Domain to be added to the reserve list.
     * @return WP_Error|boolean       True on success, WP_Error otherwise.
     */
    public function add( $fqdn = '' ) {

    }

    /**
     * Delete a domain to the Reserve list.
     *
     * @param  string           $fqdn Domain to be deleted to the reserve list.
     * @return WP_Error|boolean       True on success, WP_Error otherwise.
     */
    public function delete( $fqdn = '' ) {

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
        $reserve_domains = $wpdb->get_rows( "SELECT domain FROM {$this->reserve_table} ORDER BY domain" );

        if ( ! empty( $reserve_domains ) ) {
            $reserve_domains = array();
        }

        /**
         * May seem peculiar to cache an empty array here but as this will
         * likely be a slow changing data set, then it's pointless to keep
         * pounding the database unnecessarily.
         */
        wp_cache_add( $cache_key, $reserve_domains, 'dark-matter' );

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
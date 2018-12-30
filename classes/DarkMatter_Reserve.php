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
     * Add a domain to the Reserve list.
     *
     * @param  string           $fqdn Domain to be added to the reserve list.
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
    }

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
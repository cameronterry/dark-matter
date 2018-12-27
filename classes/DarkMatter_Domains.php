<?php

defined( 'ABSPATH' ) || die;

class DarkMatter_Domains {
    /**
     * The Domain Mapping table name for use by the various methods.
     *
     * @var string
     */
    private $dmtable = '';

    /**
     * Reference to the global $wpdb and is more for code cleaniness.
     *
     * @var boolean
     */
    private $wpdb = false;

    /**
     * Constructor
     *
     * @return void
     */
    public function __constructor() {
        global $wpdb;

        /**
         * Setup the table name for use throughout the methods.
         */
        $this->dm_table      = $wpdb->base_prefix . 'domain_mapping';
        $this->reserve_table = $wpdb->base_prefix . 'domain_reserve';

        /**
         * Store a reference to $wpdb as it will be used a lot.
         */
        $this->wpdb = $wpdb;
    }

    /**
     * Add a domain for a specific Site in WordPress.
     *
     * @param  string  $domain FQDN to be added.
     * @return boolean         True on success. False otherwise.
     */
    public function add( $fqdn = '' ) {
        if ( ! empty( $fqdn ) ) {
            return false;
        }
    }

    /**
     * Delete a domain for a specific Site in WordPress.
     *
     * @param  string  $fqdn FQDN to be deleted.
     * @return boolean       True on success. False otherwise.
     */
    public function delete( $fqdn = '' ) {
        if ( ! empty( $fqdn ) ) {
            return false;
        }
    }

    /**
     * Find a domain for a specific Site in WordPress.
     *
     * @param  string    $fqdn FQDN to search for.
     * @return DM_Domain       Domain object.
     */
    public function find( $fqdn = '' ) {
        if ( ! empty( $fqdn ) ) {
            return false;
        }

        /**
         * Attempt to find the Domain from Object Cache.
         */


        /**
         * Load from the database. Starting with domain mapping table and then
         * checking the Reserve table.
         */
    }

    /**
     * Check if a domain exists. This checks against all websites and is not
     * site specific.
     *
     * @param  string  $fqdn FQDN to search for.
     * @return boolean         True if found. False otherwise.
     */
    public function is_exist( $fqdn = '' ) {
        if ( ! empty( $fqdn ) ) {
            return false;
        }
    }

    /**
     * Check if a domain is reserved. This checks against all websites and is
     * not site specific.
     *
     * @param  string  $fqdn FQDN to search for.
     * @return boolean       True if the domain is reserved. False otherwise.
     */
    public function is_reserved( $fqdn = '' ) {
        if ( ! empty( $fqdn ) ) {
            return false;
        }
    }

    /**
     * Add a reserved domain for the Network in WordPress.
     *
     * @param  string  $fqdn FQDN to be added.
     * @return boolean       True on success. False otherwise.
     */
    public function reserve( $fqdn = '' ) {
        if ( ! empty( $fqdn ) ) {
            return false;
        }
    }

    /**
     * Find a domain for a specific Site in WordPress.
     *
     * @param  string  $dm_domain Domain object which is to be updated.
     * @return boolean            True on success. False on failure.
     */
    public function update( $dm_domain ) {
        if ( ! empty( $domain ) ) {
            return false;
        }
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
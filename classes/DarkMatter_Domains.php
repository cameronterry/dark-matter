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

    public function add( $domain = '' ) {
        if ( ! empty( $domain ) ) {
            return false;
        }
    }

    public function delete( $domain = '' ) {
        if ( ! empty( $domain ) ) {
            return false;
        }
    }

    public function find( $domain = '' ) {
        if ( ! empty( $domain ) ) {
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

    public function is_exist( $domain = '' ) {
        if ( ! empty( $domain ) ) {
            return false;
        }
    }

    public function is_reserved( $domain = '' ) {
        if ( ! empty( $domain ) ) {
            return false;
        }
    }

    public function reserve( $domain = '' ) {
        if ( ! empty( $domain ) ) {
            return false;
        }
    }

    public function update( $domain = '' ) {
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
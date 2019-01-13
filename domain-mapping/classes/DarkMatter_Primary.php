<?php

defined( 'ABSPATH' ) || die;

class DarkMatter_Primary {
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
    public function __construct() {
        global $wpdb;

        /**
         * Setup the table name for use throughout the methods.
         */
        $this->dm_table = $wpdb->base_prefix . 'domain_mapping';

        /**
         * Store a reference to $wpdb as it will be used a lot.
         */
        $this->wpdb = $wpdb;
    }

    /**
     * Retrieve the Primary domain for a Site.
     *
     * @param  integer           $site_id Site ID to retrieve the primary domain for.
     * @return DM_Domain|boolean          Returns the DM_Domain object on success. False otherwise.
     */
    public function get( $site_id = 0 ) {
        $site_id = ( empty( $site_id ) ? get_current_blog_id() : $site_id );

        /**
         * Attempt to retrieve the domain from cache.
         */
        $cache_key      = $site_id . '-primary';
        $primary_domain = wp_cache_get( $cache_key, 'dark-matter' );

        /**
         * If the Cache is unavailable, then attempt to load the domain from the
         * database and re-prime the cache.
         */
        if ( ! $primary_domain ) {
            $primary_domain = $this->wpdb->get_var( $this->wpdb->prepare( "SELECT domain FROM {$this->dm_table} WHERE is_primary = 1 AND blog_id = %s", $site_id ) );

            if ( empty( $primary_domain ) ) {
                return false;
            }

            $this->set( $site_id, $primary_domain );
        }

        /**
         * Retrieve the entire Domain object.
         */
        $db = DarkMatter_Domains::instance();
        $_domain = $db->get( $primary_domain );

        /**
         * Determine if the domain is active. If it's not, then return false.
         * The logic is currently done like this so that if there is a primary
         * domain, the database is protected by the cache detection.
         */
        if ( ! $_domain->active ) {
            return false;
        }

        return $_domain;
    }

    /**
     * Helper function to the set the cache for the primary domain for a Site.
     *
     * @param  integer $site_id Site ID to set the primary domain cache for.
     * @param  string  $domain  Domain to be stored in the cache.
     * @return void
     */
    public function set( $site_id = 0, $domain = '' ) {
        $site_id   = ( empty( $site_id ) ? get_current_blog_id() : $site_id );
        $cache_key = $site_id . '-primary';

        wp_cache_set( $cache_key, $domain, 'dark-matter' );
    }

    /**
     * Unset the primary domain for a given Site.
     *
     * @param  integer $site_id Site ID to unset the primary domain for.
     * @return boolean          True on success. False otherwise.
     */
    public function unset( $site_id = 0 ) {
        $site_id = ( empty( $site_id ) ? get_current_blog_id() : $site_id );

        $result = $this->wpdb->update( $this->dm_table, array(
            'is_primary' => false,
        ), array(
            'blog_id' => $site_id,
        ) );

        if ( ! $result ) {
            return false;
        }

        $cache_key = $site_id . '-primary';
        wp_cache_delete( $cache_key, 'dark-matter' );

        return true;
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
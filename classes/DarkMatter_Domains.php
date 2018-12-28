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
    public function __construct() {
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
     * @param  string             $domain FQDN to be added.
     * @return DM_Domain|WP_Error         True on success. False otherwise.
     */
    public function add( $fqdn = '', $is_primary = false, $is_https = false, $force = true ) {
        if ( empty( $fqdn ) ) {
            return new WP_Error( 'empty', __( 'Please include a fully qualified domain name to be added.', 'dark-matter' ) );
        }

        /**
         * Check that the FQDN is not already stored in the database.
         */
        if ( $this->is_exist( $fqdn ) ) {
            return new WP_Error( 'exists', __( 'This domain is already assigned to a Site.', 'dark-matter' ) );
        }

        if ( $is_primary ) {
            $dm_primary = DarkMatter_Primary::instance();

            $primary_domain = $dm_primary->get();

            /**
             * Check to make sure another domain isn't set to Primary (can be overridden by the --force flag).
             */
            if ( ! empty( $primary_domain ) && ! $force ) {
                return new WP_Error( 'primary', __( 'You cannot add this domain as the primary domain without using the force flag.', 'dark-matter' ) );
            } else {
                $dm_primary->unset();
            }
        }

        $_domain = array(
            'active'     => true,
            'blog_id'    => get_current_blog_id(),
            'domain'     => $fqdn,
            'is_primary' => ( ! $is_primary ? false : true ),
            'is_https'   => ( ! $is_https ? false : true ),
        );

        $result = $this->wpdb->insert( $this->dm_table, $_domain, array(
            '%d', '%d', '%s', '%d', '%d',
        ) );

        if ( $result ) {
            /**
             * Create the cache key.
             */
            $cache_key = md5( $fqdn );

            /**
             * Update the domain object prior to update the cache.
             */
            $_domain['id'] = $this->wpdb->insert_id;
            wp_cache_add( $cache_key, $_domain, 'dark-matter' );

            return new DM_Domain( (object) $_domain );
        }

        return new WP_Error( 'unknown', __( 'Sorry, the domain could not be added. An unknown error occurred.', 'dark-matter' ) );
    }

    /**
     * Delete a domain for a specific Site in WordPress.
     *
     * @param  string  $fqdn FQDN to be deleted.
     * @return boolean       True on success. False otherwise.
     */
    public function delete( $fqdn = '' ) {
        if ( empty( $fqdn ) ) {
            return false;
        }

        /**
         * Cannot delete what does not exist.
         */
        if ( ! $this->is_exist( $fqdn ) ) {
            return false;
        }

        $result = $this->wpdb->delete( $this->dm_table, array(
            'domain' => $fqdn,
        ), array( '%s' ) );

        if ( $result ) {
            $cache_key = md5( $fqdn );
            wp_cache_delete( $cache_key, 'dark-matter' );

            return true;
        }

        return false;
    }

    /**
     * Find a domain for a specific Site in WordPress.
     *
     * @param  string    $fqdn FQDN to search for.
     * @return DM_Domain       Domain object.
     */
    public function find( $fqdn = '' ) {
        if ( empty( $fqdn ) ) {
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
     * Retrieve a domain for a specific Site in WordPress.
     *
     * @param  string    $fqdn FQDN to search for.
     * @return DM_Domain       Domain object.
     */
    public function get( $fqdn = '' ) {
        if ( empty( $fqdn ) ) {
            return false;
        }

        /**
         * Attempt to retrieve the domain from cache.
         */
        $cache_key = md5( $fqdn );
        $_domain   = wp_cache_get( $cache_key, 'dark-matter' );

        /**
         * If the domain cannot be retrieved from cache, attempt to retrieve it
         * from the database.
         */
        if ( ! $_domain ) {
            $_domain = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM {$this->dm_table} WHERE domain = %s", $fqdn ) );

            if ( empty( $_domain ) ) {
                return null;
            }

            /**
             * Update the cache.
             */
            wp_cache_add( $cache_key, $_domain, 'dark-matter' );
        }

        return new DM_Domain( (object) $_domain );
    }

    /**
     * Check if a domain exists. This checks against all websites and is not
     * site specific.
     *
     * @param  string  $fqdn FQDN to search for.
     * @return boolean       True if found. False otherwise.
     */
    public function is_exist( $fqdn = '' ) {
        if ( empty( $fqdn ) ) {
            return false;
        }

        $_domain = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT id FROM {$this->dm_table} WHERE domain = %s LIMIT 1", $fqdn ) );

        return ( $_domain !== null );
    }

    /**
     * Check if a domain is reserved. This checks against all websites and is
     * not site specific.
     *
     * @param  string  $fqdn FQDN to search for.
     * @return boolean       True if the domain is reserved. False otherwise.
     */
    public function is_reserved( $fqdn = '' ) {
        if ( empty( $fqdn ) ) {
            return false;
        }

        return false;
    }

    /**
     * Add a reserved domain for the Network in WordPress.
     *
     * @param  string  $fqdn FQDN to be added.
     * @return boolean       True on success. False otherwise.
     */
    public function reserve( $fqdn = '' ) {
        if ( empty( $fqdn ) ) {
            return false;
        }

        return false;
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
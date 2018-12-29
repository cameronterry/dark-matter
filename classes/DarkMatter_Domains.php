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
     * @param  string             $fqdn       Domain to be updated.
     * @param  boolean            $is_primary Primary domain setting.
     * @param  boolean            $is_https   HTTPS protocol setting.
     * @param  boolean            $force      Whether the update should be forced.
     * @param  integer            $id         Domain record ID. Used for updating records rather than adding.
     * @return DM_Domain|WP_Error             DM_Domain on success. WP_Error on failure.
     */
    public function add( $fqdn = '', $is_primary = false, $is_https = false, $force = true, $id = 0 ) {
        if ( empty( $fqdn ) ) {
            return new WP_Error( 'empty', __( 'The fully qualified domain name is empty.', 'dark-matter' ) );
        }

        /**
         * Check that the FQDN is not already stored in the database.
         */
        if ( $this->is_exist( $fqdn ) ) {
            return new WP_Error( 'exists', __( 'This domain is already assigned to a Site.', 'dark-matter' ) );
        }

        $dm_primary = DarkMatter_Primary::instance();

        if ( $is_primary ) {
            $primary_domain = $dm_primary->get();

            /**
             * Check to make sure another domain isn't set to Primary (can be overridden by the --force flag).
             */
            if ( ! empty( $primary_domain ) && ! $force ) {
                return new WP_Error( 'primary', __( 'You cannot make this domain the primary domain without using the force flag.', 'dark-matter' ) );
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

        /**
         * Determine if we need to update a pre-existing domain or adding a
         * brand new domain.
         */
        if ( empty( $id ) ) {
            $result = $this->wpdb->insert( $this->dm_table, $_domain, array(
                '%d', '%d', '%s', '%d', '%d',
            ) );
        } else {
            $result = $this->wpdb->update( $this->dm_table, $_domain, array(
                'id' => $id,
            ) );
        }

        if ( $result ) {
            /**
             * Create the cache key.
             */
            $cache_key = md5( $fqdn );

            /**
             * Update the domain object prior to priming the cache for both the
             * domain object and the primary domain if necessary.
             */
            $_domain['id'] = $this->wpdb->insert_id;
            wp_cache_set( $cache_key, $_domain, 'dark-matter' );

            if ( $is_primary ) {
                $dm_primary->set( get_current_blog_id(), $fqdn );
            }

            return new DM_Domain( (object) $_domain );
        }

        return new WP_Error( 'unknown', __( 'An unknown error occurred.', 'dark-matter' ) );
    }

    /**
     * Delete a domain for a specific Site in WordPress.
     *
     * @param  string           $fqdn FQDN to be deleted.
     * @return WP_Error|boolean       True on success. False otherwise.
     */
    public function delete( $fqdn = '', $force = true ) {
        if ( empty( $fqdn ) ) {
            return new WP_Error( 'empty', __( 'Please include a fully qualified domain name to be removed.', 'dark-matter' ) );
        }

        /**
         * Cannot delete what does not exist.
         */
        if ( ! $this->is_exist( $fqdn ) ) {
            return new WP_Error( 'exists', __( 'The domain cannot be found.', 'dark-matter' ) );
        }

        /**
         * Check to make sure the domain is assigned to the site.
         */
        $_domain = $this->get( $fqdn );

        if ( ! $_domain || get_current_blog_id() !== $_domain->blog_id ) {
            return new WP_Error( 'not found', __( 'The domain cannot be found.', 'dark-matter' ) );
        }

        /**
         * Check to make sure that the domain is not a primary and if it is that
         * the force flag has been provided.
         */
        if ( $_domain->is_primary ) {
            if ( $force ) {
                DarkMatter_Primary::instance()->unset();
            } else {
                return new WP_Error( 'primary', __( 'This domain is the primary domain for this Site. Please provide the force flag to delete.', 'dark-matter' ) );
            }
        }

        $result = $this->wpdb->delete( $this->dm_table, array(
            'domain' => $fqdn,
        ), array( '%s' ) );

        if ( $result ) {
            $cache_key = md5( $fqdn );
            wp_cache_delete( $cache_key, 'dark-matter' );

            return true;
        }

        return new WP_Error( 'unknown', __( 'Sorry, the domain could not be deleted. An unknown error occurred.', 'dark-matter' ) );;
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
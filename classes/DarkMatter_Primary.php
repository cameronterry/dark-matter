<?php

defined( 'ABSPATH' ) || die;

class DarkMatter_Primary {
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
        $cache_key      = md5( $fqdn ) . '-primary';
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

            wp_cache_add( $cache_key, $primary_domain, 'dark-matter' );
        }

        /**
         * Retrieve the entire Domain object.
         */
        $db = DarkMatter_Domains::instance();
        return $db->get( $primary_domain );
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
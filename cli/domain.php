<?php

defined( 'ABSPATH' ) || die;

class DarkMatter_Domain_CLI {
    /**
     * Add a domain to a site on the WordPress Network.
     *
     * ### OPTIONS
     *
     * <domain>
     * : The domain you wish to add.
     *
     * [--https]
     * : Sets the protocol to be HTTPS. This is only needed when used with the --primary flag and is ignored otherwise.
     *
     * [--primary]
     * : Sets the domain to be the primary domain for the Site, the one which visitors will be redirected to.
     *
     * [--secondary]
     * : Sets the domain to be a secondary domain for the Site. Visitors will be redirected from this domain to the primary.
     *
     * ### EXAMPLES
     * Set the primary domain and set the protocol to HTTPS.
     *
     *      wp --url="sites.my.com/siteone" darkmatter domain add www.primarydomain.com --primary --https
     */
    public function add( $args, $assoc_args ) {
        if ( empty( $args[0] ) ) {
            WP_CLI::error( __( 'Please include a fully qualified domain name to be added.', 'dark-matter' ) );
        }

        $fqdn = $args[0];

        $opts = wp_parse_args( $assoc_args, [
            'force'   => false,
            'https'   => false,
            'primary' => false,
        ] );

        $db = DarkMatter_Domains::instance();

        /**
         * Check to make sure the domain isn't reserved.
         */
        if ( $db->is_reserved( $fqdn ) ) {
            WP_CLI::error( __( 'The domain has been reserved by WordPress Network administrators.', 'dark-matter' ) );
        }

        /**
         * Check to make sure the domain isn't already assigned to a site.
         */
        if ( $db->is_exist( $fqdn ) ) {
            WP_CLI::error( __( 'This domain is already assigned to a Site.', 'dark-matter' ) );
        }

        $dm_primary = DarkMatter_Primary::instance();

        $primary_domain = $dm_primary->get();

        /**
         * Check to make sure another domain isn't set to Primary (can be overridden by the --force flag).
         */
        if ( ! empty( $primary_domain ) && ! $opts['force'] ) {
            WP_CLI::error( __( 'This domain is already assigned to a Site.', 'dark-matter' ) );
        } else {
            $dm_primary->unset();
        }

        /**
         * Add the domain.
         */
        $result = $db->add( $fqdn, $opts['primary'], $opts['https'] );

        if ( ! $result ) {
            WP_CLI::error( __( 'Sorry, the domain could not be added. An unknown error occurred.', 'dark-matter' ) );
        }

        WP_CLI::success( $fqdn . __( ': was added.', 'dark-matter' ) );
    }

    /**
     * Remove a specific domain on a Site on the WordPress Network.
     *
     * ### OPTIONS
     *
     * <domain>
     * : The domain you wish to remove.
     *
     * [--force]
     * : Force Dark Matter to remove the domain. This is required if you wish to
     * remove a Primary domain from a Site.
     *
     * ### EXAMPLES
     * Remove a domain from a Site.
     *
     *      wp --url="sites.my.com/siteone" darkmatter domain remove www.primarydomain.com
     *
     * Remove a primary domain from a Site. Please note; this ***WILL NOT*** set
     * another domain to replace the Primary. You must set this using either the
     * add or set commands.
     *
     *      wp --url="sites.my.com/siteone" darkmatter domain remove www.primarydomain.com --force
     */
    public function remove( $args, $assoc_args ) {
        if ( empty( $args[0] ) ) {
            WP_CLI::error( __( 'Please include a fully qualified domain name to be removed.', 'dark-matter' ) );
        }

        $fqdn = $args[0];

        $opts = wp_parse_args( $assoc_args, [
            'force' => false,
        ] );

        $db = DarkMatter_Domains::instance();

        /**
         * Check to make sure the domain is assigned to the site.
         */
        $_domain = $db->get( $fqdn );

        if ( ! $_domain || get_current_blog_id() !== $_domain->blog_id ) {
            WP_CLI::error( __( 'The domain cannot be found.', 'dark-matter' ) );
        }

        /**
         * Check to make sure the domain is not Primary (can be overridden by the --force flag).
         */
        if ( $_domain->is_primary && ! $opts['force'] ) {
            WP_CLI::error( __( 'You cannot delete a primary domain. Use --force flag if you really want to and know what you are doing.', 'dark-matter' ) );
        }

        /**
         * Remove the domain.
         */
        $result = $db->delete( $fqdn );

        if ( ! $result ) {
            WP_CLI::error( __( 'Sorry, the domain could not be removed. An unknown error occurred.', 'dark-matter' ) );
        }

        WP_CLI::success( $fqdn . __( ': has been removed from this site', 'dark-matter' ) );
    }

    /**
     * Update the flags for a specific domain on a Site on the WordPress Network.
     *
     * ### OPTIONS
     *
     * <domain>
     * : The domain you wish to update.
     *
     * [--force]
     * : Force Dark Matter to update the domain.
     *
     * [--https]
     * : Set the protocol to be HTTPS. This is only needed when used with the
     * --primary flag and is ignored otherwise.
     *
     * [--primary]
     * : Set the domain to be the primary domain for the Site, the one which
     * visitors will be redirected to. If a primary domain is already set, then
     * you must use the --force flag to perform the update.
     *
     * [--secondary]
     * : Set the domain to be a secondary domain for the Site. Visitors will be
     * redirected from this domain to the primary.
     *
     * ### EXAMPLES
     * Set the primary domain and set the protocol to HTTPS.
     *
     *      wp --url="sites.my.com/siteone" darkmatter domain set www.primarydomain.com --primary
     *      wp --url="sites.my.com/siteone" darkmatter domain set www.primarydomain.com --secondary
     */
    public function set() {
        /** Check the domain exists for the Site. */

        /** Check to make sure the domain is not Primary (can be overridden by the --force flag). */
    }
}
WP_CLI::add_command( 'darkmatter domain', 'DarkMatter_Domain_CLI' );
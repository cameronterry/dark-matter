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

        /**
         * Add the domain.
         */
        $db = DarkMatter_Domains::instance();
        $result = $db->add( $fqdn, $opts['primary'], $opts['https'], $opts['force'] );

        if ( is_wp_error( $result ) ) {
            $error_msg = $result->get_error_message();

            if ( 'primary' === $result->get_error_code() ) {
                $error_msg = __( 'You cannot add this domain as the primary domain without using the --force flag.', 'dark-matter' );
            }

            WP_CLI::error( $error_msg );
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
         * Remove the domain.
         */
        $result = $db->delete( $fqdn, $opts['force'] );

        if ( is_wp_error( $result ) ) {
            $error_msg = $result->get_error_message();

            if ( 'primary' === $result->get_error_code() ) {
                $error_msg = __( 'You cannot delete a primary domain. Use --force flag if you really want to and know what you are doing.', 'dark-matter' );
            }

            WP_CLI::error( $error_msg );
        }

        WP_CLI::success( $fqdn . __( ': has been removed.', 'dark-matter' ) );
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
     * [--use-http]
     * : Set the protocol to be HTTP.
     *
     * [--use-https]
     * : Set the protocol to be HTTPS.
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
    public function set( $args, $assoc_args ) {
        if ( empty( $args[0] ) ) {
            WP_CLI::error( __( 'Please include a fully qualified domain name to be removed.', 'dark-matter' ) );
        }

        $fqdn = $args[0];

        $opts = wp_parse_args( $assoc_args, [
            'force'     => false,
            'use-http'  => null,
            'use-https' => null,
            'primary'   => null,
            'secondary' => null,
        ] );

        /**
         * Ensure that contradicting options are not being supplied.
         */
        if ( $opts['use-http'] && $opts['use-https'] ) {
            WP_CLI::error( __( 'A domain cannot be both HTTP and HTTPS.', 'dark-matter' ) );
        }

        if ( $opts['primary'] && $opts['secondary'] ) {
            WP_CLI::error( __( 'A domain cannot be both primary and secondary.', 'dark-matter' ) );
        }

        /**
         * Determine if we are switching between HTTP and HTTPS.
         */
        $is_https = $opts['use-https'];

        if ( $opts['use-http'] ) {
            $is_https = false;
        }

        /**
         * Determine if we are switching between primary and secondary.
         */
        $is_primary = $opts['primary'];

        if ( $opts['secondary'] ) {
            $is_primary = false;
        }

        /**
         * Update the records.
         */
        $db = DarkMatter_Domains::instance();
        $result = $db->update( $fqdn, $is_primary, $is_https, $opts['force'] );

        /**
         * Handle the output for errors and success.
         */
        if ( is_wp_error( $result ) ) {
            $error_msg = $result->get_error_message();

            if ( 'primary' === $result->get_error_code() ) {
                $error_msg = __( 'You cannot modify the primary domain. Use --force flag if you really want to and know what you are doing.', 'dark-matter' );
            }

            WP_CLI::error( $error_msg );
        }

        WP_CLI::success( $fqdn . __( ': successfully updated.', 'dark-matter' ) );
    }
}
WP_CLI::add_command( 'darkmatter domain', 'DarkMatter_Domain_CLI' );
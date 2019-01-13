<?php

defined( 'ABSPATH' ) || die;

class DarkMatter_Restrict_CLI {
    /**
     * Add a domain to the restrict for the WordPress Network.
     *
     * ### OPTIONS
     *
     * <domain>
     * : The domain you wish to add to the restrict list.
     *
     * ### EXAMPLES
     * Add a domain to the restrict list.
     *
     *      wp darkmatter restrict add www.example.com
     */
    public function add( $args, $assoc_args ) {
        if ( empty( $args[0] ) ) {
            WP_CLI::error( __( 'Please include a fully qualified domain name to be added.', 'dark-matter' ) );
        }

        $fqdn = $args[0];

        $restricted = DarkMatter_Restrict::instance();
        $result     = $restricted->add( $fqdn );

        if ( is_wp_error( $result ) ) {
            WP_CLI::error( $result->get_error_message() );
        }

        WP_CLI::success( $fqdn . __( ': is now restricted.', 'dark-matter' ) );
    }

    /**
     * Remove a domain to the restrict for the WordPress Network.
     *
     * ### OPTIONS
     *
     * <domain>
     * : The domain you wish to remove to the restrict list.
     *
     * ### EXAMPLES
     * Remove a domain to the restrict list.
     *
     *      wp darkmatter restrict remove www.example.com
     */
    public function remove( $args, $assoc_args ) {
        if ( empty( $args[0] ) ) {
            WP_CLI::error( __( 'Please include a fully qualified domain name to be added.', 'dark-matter' ) );
        }

        $fqdn = $args[0];

        $restricted = DarkMatter_Restrict::instance();
        $result     = $restricted->delete( $fqdn );

        if ( is_wp_error( $result ) ) {
            WP_CLI::error( $result->get_error_message() );
        }

        WP_CLI::success( $fqdn . __( ': is no longer restricted.', 'dark-matter' ) );
    }
}
WP_CLI::add_command( 'darkmatter restrict', 'DarkMatter_Restrict_CLI' );
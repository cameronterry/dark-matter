<?php

defined( 'ABSPATH' ) || die;

class DarkMatter_Reserve_CLI {
    /**
     * Add a domain to the reserve for the WordPress Network.
     *
     * ### OPTIONS
     *
     * <domain>
     * : The domain you wish to add to the reserve list.
     *
     * ### EXAMPLES
     * Add a domain to the reserve list.
     *
     *      wp darkmatter reserve add www.example.com
     */
    public function add( $args, $assoc_args ) {
        if ( empty( $args[0] ) ) {
            WP_CLI::error( __( 'Please include a fully qualified domain name to be added.', 'dark-matter' ) );
        }

        $fqdn = $args[0];

        $reserved = DarkMatter_Reserve::instance();
        $result   = $reserved->add( $fqdn );

        if ( is_wp_error( $result ) ) {
            WP_CLI::error( $result->get_error_message() );
        }

        WP_CLI::success( $fqdn . __( ': is now reserved.', 'dark-matter' ) );
    }

    /**
     * Remove a domain to the reserve for the WordPress Network.
     *
     * ### OPTIONS
     *
     * <domain>
     * : The domain you wish to remove to the reserve list.
     *
     * ### EXAMPLES
     * Remove a domain to the reserve list.
     *
     *      wp darkmatter reserve remove www.example.com
     */
    public function remove( $args, $assoc_args ) {
        if ( empty( $args[0] ) ) {
            WP_CLI::error( __( 'Please include a fully qualified domain name to be added.', 'dark-matter' ) );
        }

        $fqdn = $args[0];

        $reserved = DarkMatter_Reserve::instance();
        $result   = $reserved->delete( $fqdn );

        if ( is_wp_error( $result ) ) {
            WP_CLI::error( $result->get_error_message() );
        }

        WP_CLI::success( $fqdn . __( ': is no longer reserved.', 'dark-matter' ) );
    }
}
WP_CLI::add_command( 'darkmatter reserve', 'DarkMatter_Reserve_CLI' );
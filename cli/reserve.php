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

    }
}
WP_CLI::add_command( 'darkmatter reserve', 'DarkMatter_Reserve_CLI' );
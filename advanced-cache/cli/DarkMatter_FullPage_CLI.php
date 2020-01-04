<?php
defined( 'ABSPATH' ) || die();

class DarkMatter_FullPage_CLI {
    /**
     * Retrieve full page cache statistics for a specific URL.
     *
     * ### OPTIONS
     *
     * <url>
     * : Full URL to retrieve full page cache statistics.
     *
     * @param $args
     * @param $assoc_args
     */
    public function stats( $args, $assoc_args ) {
        if ( empty( $args[0] ) ) {
            WP_CLI::error( __( 'Please provide a URL to retrieve cache statistics for.', 'dark-matter' ) );
        }

        $url = $args[0];

        $cache_data = new DM_Request_Data( $url );

        var_dump( $cache_data );


        if ( ! empty( $cache_data['variants'] ) ) {
            foreach ( $cache_data['variants'] as $key => $data ) {
            }
        }
    }
}
WP_CLI::add_command( 'darkmatter fullpage', 'DarkMatter_FullPage_CLI' );
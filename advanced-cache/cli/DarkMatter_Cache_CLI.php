<?php
defined( 'ABSPATH' ) || die();

class DarkMatter_Cache_CLI {
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
        
    }
}
WP_CLI::add_command( 'darkmatter cache', 'DarkMatter_Cache_CLI' );
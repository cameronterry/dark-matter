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
     * [--format]
     * : Determine which format that should be returned. Defaults to "table" and accepts "json", "csv", "yaml", and
     * "count".
     *
     * @param $args
     * @param $assoc_args
     * @throws Exception
     */
    public function info( $args, $assoc_args ) {
        if ( empty( $args[0] ) ) {
            WP_CLI::error( __( 'Please provide a URL to retrieve cache statistics for.', 'dark-matter' ) );
        }

        $url = $args[0];

        $cache_info = new DM_Cache_Info( $url );
        $data       = $cache_info->get_all();

        $display = [
            'variant_key',
            'variant_name',
            'provider',
            'time',
            'remaining',
            'ttl',
            'size',
            'headers'
        ];

        $format = WP_CLI\Utils\get_flag_value( $assoc_args, 'format', 'table' );

        WP_CLI\Utils\format_items( $format, $data, $display );
    }

    /**
     * Invalidate a full page cache entry, either by Variant Key or URL.
     *
     * ### OPTIONS
     *
     * <url>
     * : URL to invalidate.
     *
     * [<variant_key>]
     * : Specify a specific variant to remove, if there are multiples.
     *
     * @param $args
     * @param $assoc_args
     */
    public function invalidate( $args, $assoc_args ) {
        if ( empty( $args[0] ) ) {
            WP_CLI::error( __( 'Please provide either a Variant Key or a URL to perform an invalidation.', 'dark-matter' ) );
        }

        $url = $args[0];
        $key = ( empty( $args[1] ) ? '' : $args[1] );

        $cache_entry  = new DM_Request_Cache( $url );
        $data_entry   = $cache_entry->get_data();
        $request_data = $data_entry->data();

        /**
         * Invalidate a specifc key.
         */
        if ( ! empty( $key ) ) {
            $data_entry->invalidate( $key );

            WP_CLI::success( __( 'Full Page Cache variant has been invalidated.', 'dark-matter' ) );
        }
        else if ( $request_data['count'] > 0 ) {
            /**
             * If there is more than one variant, double-check to ensure the admin wants to delete ALL the variants. If
             * there is only one, then just continue as normal.
             */
            if ( $request_data['count'] > 1 ) {
                WP_CLI::confirm( __( 'This URL has multiple cache variants and this command will clear all. Do you wish to proceed?', 'dark-matter' ), $assoc_args );
            }

            $data_entry->invalidate();

            WP_CLI::success( $request_data['count'] . __( ' cache variants deleted.', 'dark-matter' ) );
        }
        else {
            WP_CLI::error( __( 'No cache entries are available for this URL.', 'dark-matter' ) );
        }
    }
}
WP_CLI::add_command( 'darkmatter fullpage', 'DarkMatter_FullPage_CLI' );
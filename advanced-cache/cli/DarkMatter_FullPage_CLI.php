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

        $cache_data   = new DM_Request_Data( $url );
        $request_data = $cache_data->data();

        $data = [];

        foreach ( $request_data['variants'] as $variant_key => $variant_data ) {
            $expiry_time = $variant_data['time_utc'] + $variant_data['ttl_secs'];
            $remaining   = human_time_diff( time(), $expiry_time );

            if ( time() > $expiry_time ) {
                $remaining = __( 'Expired', 'dark-matter' );
            }

            $data[] = [
                'Variant Key' => $variant_key,
                'Provider'    => $request_data['provider'],
                'Time'        => $variant_data['time_utc'],
                'Remaining'   => $remaining,
                'TTL'         => human_time_diff( $variant_data['time_utc'], $expiry_time ),
                'Size'        => size_format( $variant_data['size_bytes'] ),
                'Headers'     => $variant_data['headers'],
            ];
        }

        $display = [ 'Variant Key', 'Provider', 'Time', 'Remaining', 'TTL', 'Size', 'Headers' ];

        WP_CLI\Utils\format_items( 'table', $data, $display );
    }
}
WP_CLI::add_command( 'darkmatter fullpage', 'DarkMatter_FullPage_CLI' );
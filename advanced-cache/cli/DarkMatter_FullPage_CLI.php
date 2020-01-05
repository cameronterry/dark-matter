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

        $cache_entry  = new DM_Request_Cache( $url );
        $request_data = $cache_entry->get_data()->data();

        $data = [];

        foreach ( $request_data['variants'] as $variant_key => $variant_data ) {
            $time        = $variant_data['time_utc'];
            $expiry_time = $time + $variant_data['ttl_secs'];
            $remaining   = human_time_diff( time(), $expiry_time );

            if ( time() > $expiry_time ) {
                $remaining = __( 'Expired', 'dark-matter' );
            }

            /**
             * Convert the Unix timestamp to a human readable format and in the website's current timezone.
             */
            $datetime = new DateTime( "@{$time}" );
            $datetime->setTimezone( wp_timezone() );

            $data[] = [
                'Variant Key'  => $variant_key,
                'Variant Name' => $variant_data['name'],
                'Provider'     => $request_data['provider'],
                'Time'         => $datetime->format( 'r' ),
                'Remaining'    => $remaining,
                'TTL'          => human_time_diff( $time, $expiry_time ),
                'Size'         => size_format( $variant_data['size_bytes'] ),
                'Headers'      => $variant_data['headers'],
            ];
        }

        $display = [
            'Variant Key',
            'Variant Name',
            'Provider',
            'Time',
            'Remaining',
            'TTL',
            'Size',
            'Headers'
        ];

        $format = WP_CLI\Utils\get_flag_value( $assoc_args, 'format', 'table' );

        WP_CLI\Utils\format_items( $format, $data, $display );
    }
}
WP_CLI::add_command( 'darkmatter fullpage', 'DarkMatter_FullPage_CLI' );
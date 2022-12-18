<?php
defined( 'ABSPATH' ) || die();

/**
 * Class DM_Cache_Info
 *
 * Provides useful data in a more accessible format for a particular cache entry.
 */
class DM_Cache_Info {
    /**
     * @var DM_Request_Cache Request Cache object.
     */
    private $cache_entry = null;

    /**
     * @var array Data of the Cache Entry.
     */
    private $cache_data = [];

    /**
     * @var array All cache information for the URL and each variant.
     */
    private $data = [];

    /**
     * DM_Cache_Info constructor.
     *
     * @param string $url Request URL to check for cache data.
     */
    public function __construct( $url = '' ) {
        $this->cache_entry = new DM_Request_Cache( $url );
        $this->cache_data  = $this->cache_entry->get_data()->data();

        $this->compile_data();
    }

    /**
     * Construct the overall data record.
     */
    private function compile_data() {
        if ( empty( $this->cache_data ) ) {
            return '';
        }

        foreach ( $this->cache_data['variants'] as $variant_key => $variant_data ) {
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

            $this->data[] = [
                'variant_key'  => $variant_key,
                'variant_name' => $variant_data['name'],
                'provider'     => $this->cache_data['provider'],
                'time'         => $datetime->format( 'r' ),
                'remaining'    => $remaining,
                'ttl'          => human_time_diff( $time, $expiry_time ),
                'size'         => size_format( $variant_data['size_bytes'] ),
                'headers'      => $variant_data['headers'],
            ];
        }
    }

    /**
     * Returns all the available cache information.
     *
     * @return array Cache information for the URL and all variants.
     */
    public function get_all() {
        return $this->data;
    }

    /**
     * Retrieves the standard variant.
     *
     * @return array Data record for the "standard" Variant.
     */
    public function get_standard() {
        foreach ( $this->data as $record ) {
            if ( 'standard' === $record['variant_name'] ) {
                return $record;
            }
        }

        return null;
    }

    /**
     * Returns the number of variant records.
     *
     * @return int Number of Variant records.
     */
    public function get_variant_count() {
        return count( $this->data );
    }
}
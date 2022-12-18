<?php
defined( 'ABSPATH' ) || die;

/**
 * Class DM_Request_Data
 *
 * Handles the data "record" - a serialised array of data in Object Cache - for a specific request and all variants.
 * This uses the base URL as the primary key for retrieving the data.
 */
class DM_Request_Data {
    /**
     * @var array Data record.
     */
    private $data = [];

    /**
     * @var string Cache Key.
     */
    private $key = '';

    /**
     * DM_Request_Data constructor.
     *
     * @param string $base_url URL to retrieve Cache data for.
     */
    public function __construct( $base_url = '' ) {
        $this->key = md5( $base_url );

        $data = wp_cache_get( $this->key, 'dark-matter-fullpage-data' );

        $this->data = [
            'count'    => 0,
            'provider' => 'dark-matter',
            'variants' => [],
        ];

        if ( is_array( $data ) ) {
            $this->data = array_merge( $this->data, $data );
        }
    }

    /**
     * Returns the Request Cache Data.
     *
     * @return array Request Cache Data.
     */
    public function data() {
        return $this->data;
    }

    /**
     * Invalidates the cache for the current data record. Invalidates all variants unless a variant key is provided.
     *
     * @param string $variant_key Invalidate a specific key.
     */
    public function invalidate( $variant_key = '' ) {
        if ( ! empty( $variant_key ) && ! empty( $this->data['variants'][ $variant_key ] ) ) {
            wp_cache_delete( $variant_key, 'dark-matter-fullpage' );

            /**
             * Remove from the list of variants.
             */
            unset( $this->data['variants'][ $variant_key ] );
        } else {
            foreach ( $this->data['variants'] as $key => $data ) {
                wp_cache_delete( $key, 'dark-matter-fullpage' );
            }

            /**
             * Empty the variants.
             */
            $this->data['variants'] = [];
        }

        /**
         * Auto-save, as this method removes records.
         */
        $this->save();
    }

    /**
     * Update the Request Cache Record.
     *
     * @return bool True on success. False otherwise.
     */
    public function save() {
        $this->data['count'] = count( $this->data['variants'] );

        return wp_cache_set( $this->key, $this->data, 'dark-matter-fullpage-data' );
    }

    /**
     * Add a variant to the Request Cache Data record.
     *
     * @param string  $variant_key  Variant key to be added.
     * @param string  $variant_name Variant name to be added.
     * @param array   $cache_data   Useful data of the variant.
     * @param integer $ttl          Length of time the output will be cached.
     */
    public function variant_add( $variant_key = '', $variant_name = 'standard', $cache_data = [], $ttl = 0 ) {
        $variant_data = [
            'name'     => $variant_name,
            'time_utc' => time(),
            'ttl_secs' => $ttl,
        ];

        /**
         * Store the size of the HTML.
         */
        if ( ! empty( $cache_data['body'] ) ) {
            $variant_data['size_bytes'] = strlen( $cache_data['body'] );
        }

        /**
         * Added a header count.
         */
        if ( ! empty( $cache_data['headers'] ) ) {
            $variant_data['headers'] = count( $cache_data['headers'] );
        }

        /**
         * Store whether the request being cached is a redirect.
         */
        if ( ! empty( $cache_data['redirect'] ) ) {
            $variant_data['is_redirect'] = strlen( $cache_data['redirect'] );
        }

        /**
         * Add the Variant Data to the Request Data object.
         */
        $this->data['variants'][ $variant_key ] = $variant_data;
    }

    /**
     * Remove a variant to the Request Cache Data record.
     *
     * @param string $variant_key Variant key to be removed.
     */
    public function variant_remove( $variant_key = '' ) {
        if ( ! array_key_exists( $variant_key, $this->data['variants'] ) ) {
            unset( $this->data['variants'][ $variant_key ] );
        }
    }
}
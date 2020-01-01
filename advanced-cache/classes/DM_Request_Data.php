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
     * Invalidates the cache for the current data record. Invalidates all variants unless a variant key is provided.
     *
     * @param string $variant_key Invalidate a specific key.
     */
    public function invalidate( $variant_key = '' ) {
        if ( empty( $variant_key ) && in_array( $variant_key, $this->data['variants'], true ) ) {
            wp_cache_delete( $variant_key, 'dark-matter-fullpage' );

            /**
             * Remove from the list of variants.
             */
            $position = array_search( $variant_key, $this->data['variants'], true );
            unset( $this->data['variants'][ $position ] );
        } else {
            foreach ( $this->data['variants'] as $key ) {
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
     * @param string $variant_key Variant key to be added.
     */
    public function variant_add( $variant_key = '' ) {
        if ( ! in_array( $variant_key, $this->data['variants'], true ) ) {
            $this->data['variants'][] = $variant_key;
        }
    }

    /**
     * Remove a variant to the Request Cache Data record.
     *
     * @param string $variant_key Variant key to be removed.
     */
    public function variant_remove( $variant_key = '' ) {
        $pos = array_search( $variant_key, $this->data['variants'], true );

        if ( false !== $pos ) {
            unset( $this->data['variants'][ $pos ] );
        }
    }
}
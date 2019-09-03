<?php
defined( 'ABSPATH' ) || die;

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
     * @var array
     */
    private $variants = [];

    /**
     * DM_Request_Data constructor.
     *
     * @param string $base_url URL to retrieve Cache data for.
     */
    public function __construct( $base_url = '' ) {
        $this->key = md5( $base_url );

        $data = wp_cache_get( $this->key, 'dark-matter-fullpage' );

        $this->data = [
            'count'    => 0,
            'provider' => 'dark-matter',
            'variants' => [],
        ];

        if ( is_array( $data ) ) {
            $this->data = array_merge( $data, $this->data );
        }
    }

    /**
     * Update the Request Cache Record.
     *
     * @return bool True on success. False otherwise.
     */
    public function save() {
        $this->data['count']    = count( $this->variants );
        $this->data['variants'] = $this->variants;

        return wp_cache_set( $this->key, $this->data, 'dark-matter-fullpage' );
    }

    /**
     * Add a variant to the Request Cache Data record.
     *
     * @param string $variant_key Variant key to be added.
     */
    public function variant_add( $variant_key = '' ) {
        if ( ! in_array( $variant_key, $this->variants, true ) ) {
            $this->variants[] = $variant_key;
        }
    }

    /**
     * Remove a variant to the Request Cache Data record.
     *
     * @param string $variant_key Variant key to be removed.
     */
    public function variant_remove( $variant_key = '' ) {
        $pos = array_search( $variant_key, $this->variants, true );

        if ( false !== $pos ) {
            unset( $this->variants[ $pos ] );
        }
    }
}
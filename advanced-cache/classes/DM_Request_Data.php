<?php
defined( 'ABSPATH' ) || die;

class DM_Request_Data {
    /**
     * @var string Cache Key.
     */
    private $cache_key = '';

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
        $this->cache_key = md5( $base_url );
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
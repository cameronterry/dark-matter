<?php
defined( 'ABSPATH' ) || die;

class DM_Request_Data {
    /**
     * @var string Cache Key.
     */
    private $cache_key = '';

    /**
     * DM_Request_Data constructor.
     *
     * @param string $base_url URL to retrieve Cache data for.
     */
    public function __construct( $base_url = '' ) {
        $this->cache_key = md5( $base_url );
    }
}
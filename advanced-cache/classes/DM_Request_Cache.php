<?php
defined( 'ABSPATH' ) || die;

class DM_Request_Cache {
    /**
     * @var string Cache Key for the URL - minus any variants - of the Request.
     */
    private $url_cache_key = '';

    /**
     * DM_Request_Cache constructor.
     *
     * @param string $url URL to retrieve the Request Cache Entry.
     */
    public function __construct( $url = '' ) {
    }

    /**
     * Delete the Request Cache Entry.
     */
    public function delete() {

    }

    /**
     * Retrieve the Request Cache Entry - if available - and return it.
     */
    public function get() {

    }

    /**
     * Store the generate HTML in cache.
     *
     * @param string $output HTML to be added to the Request Cache entry.
     */
    public function set( $output = '' ) {
        

        return md5( $host . '/' . $path );
    }
}
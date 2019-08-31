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
        $this->url_cache_key = $this->get_url_key();
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
     * Generates a URL Key for the Request. This can be used to retrieve
     *
     * @return string MD5 hash key.
     */
    public function get_url_key() {
        $host = rtrim( trim( $_SERVER['HTTP_HOST'] ), '/' );
        $path = trim( strtok( $_SERVER['REQUEST_URI'], '?' ) );

        return md5( $host . '/' . $path );
    }

    /**
     * Store the generate HTML in cache.
     *
     * @param string $output HTML to be added to the Request Cache entry.
     */
    public function set( $output = '' ) {

    }
}
<?php
defined( 'ABSPATH' ) || die;

class DM_Request_Cache {
    /**
     * @var string Cache Key.
     */
    private $key = '';

    /**
     * @var string Request URL.
     */
    private $url = '';

    /**
     * @var string Cache Key for the URL - minus any variants - of the Request.
     */
    private $url_cache_key = '';

    /**
     * @var string Key distinguishing the variant.
     */
    private $variant_key = '';

    /**
     * DM_Request_Cache constructor.
     */
    public function __construct() {
        $this->set_url_and_key();

        $this->set_variant_key();

        $this->set_key();
    }

    /**
     * Delete the Request Cache Entry.
     */
    public function delete() {
        return wp_cache_delete( $this->key, 'dark-matter-fullpage' );
    }

    /**
     * Retrieve the Request Cache Entry - if available - and return it.
     */
    public function get() {
        return wp_cache_get( $this->key, 'dark-matter-fullpage' );
    }

    /**
     * Store the generate HTML in cache.
     *
     * @param string $output HTML to be added to the Request Cache entry.
     */
    public function set( $output = '' ) {

    }

    /**
     * Sets the cache key for storing the request.
     */
    public function set_key() {
        $this->key = $this->url_cache_key;

        /**
         * Append the Variant Key if there is one.
         */
        if ( ! empty( $this->variant_key ) ) {
            $this->key .= '-' . $this->variant_key;
        }
    }

    /**
     * Generates a URL Key for the Request. This can be used to retrieve
     *
     * @return string MD5 hash key.
     */
    private function set_url_and_key() {
        $host = rtrim( trim( $_SERVER['HTTP_HOST'] ), '/' );
        $path = trim( strtok( $_SERVER['REQUEST_URI'], '?' ) );

        $this->url           = $host . '/' . $path;
        $this->url_cache_key = md5( $this->url );
    }

    /**
     * Allows third parties to determine if the request should be treated differently from the standard caching logic.
     *
     * @return string MD5 hash key for the Variant.
     */
    private function set_variant_key() {
        $variant = apply_filters( 'dark_matter_request_variant', '', $this->url, $this->url_cache_key );

        $this->variant_key = md5( strval( $variant ) );
    }
}
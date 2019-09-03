<?php
defined( 'ABSPATH' ) || die;

class DM_Request_Cache {
    /**
     * @var string Cache Key.
     */
    private $key = '';

    /**
     * @var DM_Request_Data Request Cache Data.
     */
    private $request_data = null;

    /**
     * @var string Request URL.
     */
    private $url = '';

    /**
     * @var string Base URL, minus any query string parameters.
     */
    private $url_base = '';

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
     *
     * @param string $url URL to work with for the Request Cache object.
     */
    public function __construct( $url = '' ) {
        $this->url = $url;
        $this->url_base = strtok( $url, '?' );

        $this->set_url_key();

        $this->set_variant_key();

        $this->set_key();

        $this->request_data = new DM_Request_Data( $this->url_base );
    }

    /**
     * Delete the Request Cache Entry.
     *
     * @return bool True on success. False otherwise.
     */
    public function delete() {
        $this->request_data->variant_remove( $this->key );

        return wp_cache_delete( $this->key, 'dark-matter-fullpage' );
    }

    /**
     * Retrieve the Request Cache Entry - if available - and return it.
     *
     * @return bool|mixed HTML if available. False otherwise.
     */
    public function get() {
        return wp_cache_get( $this->key, 'dark-matter-fullpage' );
    }

    /**
     * Take headers and put them in a structure that is more consistent and more programmatically appeasing to use.
     *
     * @param  array $headers Raw headers.
     * @return array          Sanitized headers.
     */
    private function sanitize_headers( $headers = [] ) {
        $cache_headers = [];

        foreach ( $headers as $header ) {
            list( $key, $value ) = array_map( 'trim', explode( ':', $header, 2 ) );
            $cache_headers[ $key ] = $value;
        }

        return $cache_headers;
    }

    /**
     * Store the generate HTML in cache.
     *
     * @param  string     $output  HTML to be added to the Request Cache entry.
     * @param  array      $headers Headers to be added to Request Cache entry.
     * @return array|bool          Cache data on success. False otherwise.
     */
    public function set( $output = '', $headers = [] ) {
        /**
         * No output, no caching.
         */
        if ( empty( $output ) ) {
            return false;
        }

        $data = [
            'body'     => $output,
            'headers'  => $this->sanitize_headers( $headers ),
            'redirect' => false,
        ];

        if ( wp_cache_set( $this->key, $data, 'dark-matter-fullpage', 1 * MINUTE_IN_SECONDS ) ) {
            $this->request_data->variant_add( $this->key );
            $this->request_data->save();

            return $data;
        }

        return false;
    }

    /**
     * Create / Update a Request Cache entry for a redirect.
     *
     * @param  integer    $http_code Redirect code such as 301 or 302.
     * @param  string     $location  Destination for the redirect.
     * @param  array      $headers   Headers to be added to Request Cache entry.
     * @return array|bool            Cache data on success. False otherwise.
     */
    public function set_redirect( $http_code = 0, $location = '', $headers = [] ) {
        $data = [
            'body'      => '',
            'headers'   => $this->sanitize_headers( $headers ),
            'http_code' => $http_code,
            'location'  => $location,
            'redirect'  => true,
        ];

        if ( wp_cache_set( $this->key, $data, 'dark-matter-fullpage' ) ) {
            $this->request_data->variant_add( $this->key );
            $this->request_data->save();

            return $data;
        }

        return false;
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
    private function set_url_key() {
        $query_string = parse_url( $this->url, PHP_URL_QUERY );

        if ( empty( $query_string ) ) {
            $this->url_cache_key = md5( $this->url );
            return;
        }

        /**
         * Populate and provide an override capability for ignoring query string parameters. This can be used to prevent
         * thrashing of the full page cache by telling Dark Matter to ignore query string parameters.
         */
        $ignore_list = [ 'utm_campaign', 'utm_content', 'utm_medium', 'utm_source', 'utm_term' ];
        $ignore_list = apply_filters( 'dark_matter_querystring_ignore', $ignore_list, $this->url );

        /**
         * Handle a special case value. If the $ignore_list array is the string "all", then Dark Matter will ignore all
         * query strings and store the request under the base URL instead.
         */
        if ( 'all' === $ignore_list ) {
            $this->url_cache_key = md5( $this->url_base );
            return;
        }

        /**
         * Process the query string parameters and remove those which are part of the ignore list.
         */
        parse_str( $query_string, $parameters );

        foreach ( $parameters as $key => $value ) {
            if ( in_array( $key, $ignore_list, true ) ) {
                unset( $parameters[ $key ] );
            }
        }

        $cache_key = $this->url_base;

        if ( ! empty( $parameters ) ) {
            $cache_key .= '?' . http_build_query( $parameters );
        }

        $this->url_cache_key = md5( $cache_key );
    }

    /**
     * Allows third parties to determine if the request should be treated differently from the standard caching logic.
     *
     * @return string MD5 hash key for the Variant.
     */
    private function set_variant_key() {
        $variant = apply_filters( 'dark_matter_request_variant', '', $this->url, $this->url_cache_key );

        if ( empty( $variant ) ) {
            $this->variant_key = '';
        } else {
            $this->variant_key = md5( strval( $variant ) );
        }
    }
}
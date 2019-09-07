<?php
defined( 'ABSPATH' ) || die;

class DM_Cache_Post {
    /**
     * @var array A list of URLs to cache immediately.
     */
    private $cache = [];

    /**
     * @var array A list of URLs to invalidate.
     */
    private $invalidation = [];

    /**
     * DM_Save_Post constructor.
     */
    public function __construct() {
        add_action( 'shutdown', [ $this, 'do_cache' ] );
        add_action( 'shutdown', [ $this, 'do_invalidate' ] );
    }

    /**
     * A URL to cache immediately before the process ends. This will use cURL and add delay to processing.
     *
     * @param int $id_or_url Post ID or URL.
     */
    public function cache( $id_or_url = 0 ) {
        if ( empty( $id_or_url ) ) {
            return;
        }
    }

    /**
     * Perform all the immediate cache entries.
     */
    public function do_cache() {

    }

    /**
     * Perform all cache invalidation entries.
     */
    public function do_invalidate() {
        if ( empty( $this->invalidation ) || ! is_array( $this->invalidation ) ) {
            return;
        }

        $request = null;

        foreach ( $this->invalidation as $url ) {
            $request = new DM_Request_Cache( $url );
            $request->invalidate();
        }
    }

    /**
     * Add URL to invalidate the cache before the process ends.
     *
     * @param int $id_or_url Post ID or URL.
     */
    public function invalidate( $id_or_url = 0 ) {
        if ( empty( $id_or_url ) ) {
            return;
        }

        /**
         * Handle a Post ID being provided by retrieving the Permalink.
         */
        if ( is_numeric( $id_or_url ) ) {
            $id_or_url = get_permalink( $id_or_url );

            if ( empty( $id_or_url ) ) {
                return;
            }
        }

        $this->invalidation[] = $id_or_url;
    }

    /**
     * Return the Singleton Instance of the class.
     *
     * @return bool|DM_Cache_Post
     */
    public static function instance() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }
}
DM_Cache_Post::instance();
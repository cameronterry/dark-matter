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
        /**
         * Handle post creation and edits. We attempt to run this as late as possible to ensure plugins have a change to
         * make changes before add entries to invalidate the cache.
         */
        add_action( 'clean_post_cache', [ $this, 'handle_save_post' ], 999, 1 );

        /**
         * Prioritise invalidating cache entries before attempting to instantly cache again.
         */
        add_action( 'shutdown', [ $this, 'do_cache' ], 20 );
        add_action( 'shutdown', [ $this, 'do_invalidate' ], 10 );
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
            $request = new DM_Request_Data( $url );
            $request->invalidate();
        }
    }

    /**
     * Handle the Save Post action.
     *
     * @param int     $post_id
     * @param WP_Post $post
     * @param bool    $update
     */
    public function handle_save_post( $post_id = 0, $post = null, $update = false ) {
        if ( empty( $post ) ) {
            $post = get_post( $post_id );
        }

        if (
                empty( $post )
            ||
                'revision' === $post->post_type
            ||
                ! in_array( get_post_status( $post_id ), [ 'publish', 'trash' ], true )
        ) {
            return;
        }

        /**
         * Invalidate the post itself.
         */
        $this->invalidate( $post_id );

        /**
         * Invalidate the homepage and corresponding RSS feed.
         */
        $this->invalidate( home_url( '/' ) );
        $this->invalidate( home_url( '/feed/' ) );
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

        $this->invalidation[] = $this->prepare_url( $id_or_url );
    }

    /**
     * Prepares the URL for use in caching or invalidation.
     *
     * @param  string $url URL to prepare.
     * @return string      Prepared URL.
     */
    private function prepare_url( $url = '' ) {
        if ( class_exists( 'DM_URL' ) ) {
            $url = DM_URL::instance()->map( $url );
        }

        return $url;
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
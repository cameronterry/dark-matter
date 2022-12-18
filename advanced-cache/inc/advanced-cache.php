<?php
defined( 'ABSPATH' ) || die;

/**
 * Only cache requests which are GET or HEAD.
 */
if ( ! empty( $_SERVER['REQUEST_METHOD'] ) && ! in_array( $_SERVER['REQUEST_METHOD'], array( 'GET', 'HEAD' ) ) ) {
    return;
}

/**
 * Attempt to include the Object Cache (if it was not already).
 */
if ( ! include_once( WP_CONTENT_DIR . '/object-cache.php' ) ) {
    return;
}

/**
 * Attempt to instantiate the cache and bail if it doesn't work.
 */
wp_cache_init();

if ( ! is_object( $wp_object_cache ) ) {
    return;
}

wp_cache_add_global_groups( 'dark-matter-fullpage' );
wp_cache_add_global_groups( 'dark-matter-fullpage-data' );

/**
 * Prior to loading the library and processing the cache, determine if the current installation includes a file for
 * extending Dark Matter Fullpage Caching.
 */
if ( defined( 'WP_CONTENT_DIR' ) ) {
    $extension = WP_CONTENT_DIR . '/mu-plugins/advanced-cache.php';

    if ( is_readable( $extension ) ) {
        include_once $extension;
    }
}

/**
 * Sanity check; as we offer extensibility, there will be a temptation to include WPDB. The problem with using database
 * queries at this level, is that will nullify any performance and / or scalability benefits afforded by caching in the
 * first place.
 */
if ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! empty( $GLOBALS['wpdb'] ) ) {
    trigger_error( 'Please be aware that by using database calls for Advanced Cache removes any benefit of using it, both in performance and scalability.', E_WARNING );
}

/**
 * Cannot utilise plugin_dir_path() as the inner function used is not available and this is preferable to include more
 * files than is realistically needed.
 */
$dirname = str_replace( '/inc', '', dirname( __FILE__ ) );

require_once $dirname . '/classes/DM_Request_Cache.php';
require_once $dirname . '/classes/DM_Request_Data.php';
require_once $dirname . '/classes/DM_Advanced_Cache.php';
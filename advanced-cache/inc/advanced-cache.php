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

/**
 * Cannot utilise plugin_dir_path() as the inner function used is not available and this is preferable to include more
 * files than is realistically needed.
 */
$dirname = str_replace( '/inc', '', dirname( __FILE__ ) );

require_once $dirname . '/classes/DM_Advanced_Cache.php';
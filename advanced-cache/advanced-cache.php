<?php
defined( 'ABSPATH' ) || die;

/**
 * This is more for ensuring that defining the constant, DARKMATTER_FULLPAGECACHE, does not result in an error / notice.
 * If you are reading this and wish to disable full page caching, then use WP_CACHE instead.
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/#cache Editing wp-config.php - Cache sub-section
 */
if ( ! defined( 'DARKMATTER_FULLPAGECACHE' ) ) {
    define( 'DARKMATTER_FULLPAGECACHE', true );
}

$cache_path = ( dirname( __FILE__ ) . '/plugins/dark-matter/advanced-cache/inc/advanced-cache.php' );

if ( is_readable( $cache_path ) ) {
    require_once( $cache_path );
}

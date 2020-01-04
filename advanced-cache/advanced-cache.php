<?php
defined( 'ABSPATH' ) || die;

$cache_path = ( dirname( __FILE__ ) . '/plugins/dark-matter/advanced-cache/inc/advanced-cache.php' );

if ( is_readable( $cache_path ) ) {
    require_once( $cache_path );
}

<?php
defined( 'ABSPATH' ) || die;

/**
 * Only cache requests which are GET or HEAD.
 */
if ( ! empty( $_SERVER['REQUEST_METHOD'] ) && ! in_array( $_SERVER['REQUEST_METHOD'], array( 'GET', 'HEAD' ) ) ) {
    return;
}

/**
 * Cannot utilise plugin_dir_path() as the inner function used is not available and this is preferable to include more
 * files than is realistically needed.
 */
$dirname = str_replace( '/inc', '', dirname( __FILE__ ) );

require_once $dirname . '/classes/DM_Advanced_Cache.php';
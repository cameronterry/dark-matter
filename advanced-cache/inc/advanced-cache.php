<?php
defined( 'ABSPATH' ) || die;

/**
 * Only cache requests which are GET or HEAD.
 */
if ( ! empty( $_SERVER['REQUEST_METHOD'] ) && ! in_array( $_SERVER['REQUEST_METHOD'], array( 'GET', 'HEAD' ) ) ) {
    return;
}


DM_Advanced_Cache::instance();
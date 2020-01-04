<?php
defined( 'ABSPATH' ) || die;

/**
 * Do not load the full page caching logic unless the current installation has the Dark Matter version of
 * advanced-cache.php, which defines the constant; DARKMATTER_FULLPAGECACHE.
 */
if ( ! defined( 'DARKMATTER_FULLPAGECACHE' ) ) {
    return;
}

wp_cache_add_global_groups( 'dark-matter-fullpage' );
wp_cache_add_global_groups( 'dark-matter-fullpage-data' );

require_once DM_PATH . 'advanced-cache/classes/DM_Cache_Post.php';
require_once DM_PATH . 'advanced-cache/classes/DM_Request_Data.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    require_once DM_PATH . 'advanced-cache/cli/DarkMatter_FullPage_CLI.php';
}
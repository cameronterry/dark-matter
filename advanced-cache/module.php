<?php
defined( 'ABSPATH' ) || die;

/**
 * Do not load the full page caching logic unless the current installation has the Dark Matter version of
 * advanced-cache.php, which defines the constant; DARKMATTER_FULLPAGECACHE.
 *
 * WP-CLI does not include the advanced-cache.php file as part of the bootstrap and therefore we need to pretend the
 * Dark Matter cache is enabled.
 */
if ( ! defined( 'DARKMATTER_FULLPAGECACHE' ) && ! defined( 'WP_CLI' ) ) {
    return;
}

wp_cache_add_global_groups( 'dark-matter-fullpage' );
wp_cache_add_global_groups( 'dark-matter-fullpage-data' );

require_once DM_PATH . 'advanced-cache/classes/DM_Cache_Admin_UI.php';
require_once DM_PATH . 'advanced-cache/classes/DM_Cache_Info.php';
require_once DM_PATH . 'advanced-cache/classes/DM_Cache_Post.php';
require_once DM_PATH . 'advanced-cache/classes/DM_Request_Cache.php';
require_once DM_PATH . 'advanced-cache/classes/DM_Request_Data.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    $cache_config_path = WP_CONTENT_DIR . '/advanced-cache-config.php';

    if ( is_readable( $cache_config_path ) ) {
        require_once( $cache_config_path );
    }

    require_once DM_PATH . 'advanced-cache/cli/DarkMatter_FullPage_CLI.php';
    require_once DM_PATH . 'advanced-cache/cli/DarkMatter_FullPage_Dropin_CLI.php';
}
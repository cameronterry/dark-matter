<?php
defined( 'ABSPATH' ) || die;

wp_cache_add_global_groups( 'dark-matter-fullpage-data' );

require_once DM_PATH . '/advanced-cache/DM_Request_Data.php';
require_once DM_PATH . '/advanced-cache/DM_Save_Post.php';
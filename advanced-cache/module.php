<?php
defined( 'ABSPATH' ) || die;

wp_cache_add_global_groups( 'dark-matter-fullpage-data' );

require_once DM_PATH . 'advanced-cache/classes/DM_Cache_Post.php';
require_once DM_PATH . 'advanced-cache/classes/DM_Request_Data.php';

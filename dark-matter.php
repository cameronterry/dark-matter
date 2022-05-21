<?php
/**
 * Plugin Name: Dark Matter
 * Plugin URI: https://github.com/cameronterry/dark-matter
 * Description: A highly opinionated domain mapping plugin for WordPress.
 * Version: 2.3.2
 * Author: Cameron Terry
 * Author URI: https://github.com/cameronterry/
 * Text Domain: dark-matter
 * Network: True
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * GitHub Plugin URI: https://github.com/cameronterry/dark-matter
 *
 * Dark Matter is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Dark Matter is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Dark Matter. If not, see
 * https://github.com/cameronterry/dark-matter/blob/master/license.txt.
 *
 * @package DarkMatter
 */

/** A bit of security for those who are too clever for their own good. */
defined( 'ABSPATH' ) || die;

/** Setup the Plugin Constants */
define( 'DM_PATH', plugin_dir_path( __FILE__ ) );
define( 'DM_VERSION', '2.3.2' );
define( 'DM_DB_VERSION', '20210517' );

define( 'DM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Define global cache groups and other cache related settings for all modules.
 */
wp_cache_add_global_groups(
	[
		'dark-matter',
		'dark-matter-fpc-responses',
		'dark-matter-fpc-request',
	]
);

/**
 * Include the PSR-4 autoloader.
 */
if ( file_exists( DM_PATH . 'vendor/autoload.php' ) ) {
	require_once DM_PATH . 'vendor/autoload.php';
}

require_once DM_PATH . '/dark-matter/class-dm-pluginupdate.php';
new DM_PluginUpdate();

/**
 * Domain Mapping module.
 */
require DM_PATH . '/domain-mapping/domain-mapping.php';

/**
 * Let the magic - and bugs ... probably bugs! - begin.
 */
\DarkMatter\DarkMatter::instance();

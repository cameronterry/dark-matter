<?php
/**
 * Plugin Name: Advanced Cache by Dark Matter
 * Plugin URI: https://github.com/cameronterry/dark-matter
 * Description: A version of advanced-cache.php drop-in plugin used in conjunction with Dark Matter full pge cache.
 * Author: Cameron Terry
 * Author URI: https://github.com/cameronterry/
 * Text Domain: dark-matter
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @since 3.0.0
 *
 * @package DarkMatter
 */

/**
 * Make sure Object Cache is available.
 */
global $wp_object_cache;
if ( ! is_a( $wp_object_cache, 'WP_Object_Cache' ) && ! ! include_once( WP_CONTENT_DIR . '/object-cache.php' ) ) {
	wp_cache_init();
	wp_cache_add_global_groups( 'dark-matter-fpc-cacheentries' );
}

$darkmatter_path = ( dirname( __FILE__ ) . '/plugins/dark-matter/' );

/**
 * Bootstrap the AdvancedCache.
 */
if ( file_exists( $darkmatter_path . 'vendor/autoload.php' ) ) {
	require_once $darkmatter_path . 'vendor/autoload.php';

	/**
	 * Attempt to load any customisations.
	 */
	$darkmatter_customisations_path = dirname( __FILE__ ) . '/mu-plugins/dark-matter-customisations/dark-matter.php';
	if ( file_exists( $darkmatter_customisations_path ) ) {
		require_once $darkmatter_customisations_path;
	}

	/**
	 * Fire the Advanced Cache and let Dark Matter handle the request.
	 */
	new \DarkMatter\AdvancedCache\Processor\AdvancedCache();
}

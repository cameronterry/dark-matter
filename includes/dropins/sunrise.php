<?php
/**
 * Plugin Name: Sunrise by Dark Matter Plugin
 * Plugin URI: https://github.com/cameronterry/dark-matter
 * Description: A version of sunrise.php drop-in plugin used in conjunction with Dark Matter Plugin domain mapping.
 * Version: 2.6.0
 * Author: Cameron Terry
 * Author URI: https://github.com/cameronterry/
 * Text Domain: dark-matter
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @since 2.0.0
 *
 * @package DarkMatterPlugin
 */

/**
 * Simple function to load the PSR-4 autoloader and the Sunrise logic without polluting global variables.
 *
 * @return void
 */
function darkmatterplugin_sunrise() {
	$darkmatterplugin_path = ( dirname( __FILE__ ) . '/plugins/dark-matter/' );

	/**
	 * Include the PSR-4 autoloader.
	 */
	if ( file_exists( $darkmatterplugin_path . 'vendor/autoload.php' ) ) {
		require_once $darkmatterplugin_path . 'vendor/autoload.php';
	}

	new \DarkMatterPlugin\DomainMapping\Mapping\Sunrise( $darkmatterplugin_path );
}
darkmatterplugin_sunrise();

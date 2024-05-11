<?php
/**
 * Plugin Name: Sunrise by Dark Matter
 * Plugin URI: https://github.com/cameronterry/dark-matter
 * Description: A version of sunrise.php drop-in plugin used in conjunction with Dark Matter domain mapping.
 * Author: Cameron Terry
 * Author URI: https://github.com/cameronterry/
 * Text Domain: dark-matter
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @since 2.0.0
 *
 * @package DarkMatter
 */

$dirname = ( dirname( __FILE__ ) . '/plugins/dark-matter/' );

/**
 * Include the PSR-4 autoloader.
 */
if ( file_exists( $dirname . 'vendor/autoload.php' ) ) {
	require_once $dirname . 'vendor/autoload.php';
}

\DarkMatter\DomainMapping\Installer::prepare_tables();
new \DarkMatter\DomainMapping\Sunrise();

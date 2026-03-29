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

$sunrise_path = ( dirname( __FILE__ ) . '/plugins/dark-matter/domain-mapping/inc/sunrise.php' );

if ( is_readable( $sunrise_path ) ) {
	require_once $sunrise_path;
}

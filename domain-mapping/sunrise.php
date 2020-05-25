<?php
/**
 * Sunrise dropin for use in the wp-content/ directory.
 *
 * @package DarkMatter
 */

$sunrise_path = ( dirname( __FILE__ ) . '/plugins/dark-matter/domain-mapping/inc/sunrise.php' );

if ( is_readable( $sunrise_path ) ) {
	require_once $sunrise_path;
}

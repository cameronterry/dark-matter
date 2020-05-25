<?php
/**
 * Helper file to provide basic compatibilty for some popular plugins.
 *
 * @package DarkMatter
 */

defined( 'ABSPATH' ) || die();

/**
 * Add support for logins on mapped domains for WooCommerce and bbPress.
 *
 * @return void
 */
function dark_matter_compat_allow_logins() {
	if (
		/**
		 * Detect if WooCommerce is installed.
		 */
		class_exists( 'WooCommerce' )
	||
		/**
		 * Detect if bbPress is installed.
		 */
		class_exists( 'bbPress' )
	) {
		add_filter( 'darkmatter_allow_logins', '__return_true' );
	}
}
add_action( 'init', 'dark_matter_compat_allow_logins' );

<?php
/**
 * Helper file to provide basic compatibilty for some popular plugins.
 *
 * @package DarkMatter
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || die();

/**
 * Add support for logins on mapped domains for WooCommerce and bbPress.
 *
 * @since 2.0.0
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

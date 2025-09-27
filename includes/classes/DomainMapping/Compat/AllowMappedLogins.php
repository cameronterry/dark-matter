<?php
/**
 * Helper file to provide basic compatibilty for some popular plugins.
 *
 * @package DarkMatter
 * @since 2.0.0
 */

namespace DarkMatterPlugin\DomainMapping\Compat;

use DarkMatterPlugin\Registerable;

/**
 * Class AllowMappedLogins
 */
class AllowMappedLogins implements Registerable {

	/**
	 * Can class register.
	 *
	 * @return true
	 */
	public function can_register() {
		return true;
	}

	/**
	 * Add support for logins on mapped domains for WooCommerce and bbPress.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	function allow_logins() {
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

	/**
	 * Handle actions and filters for Allow Mapped Logins.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', [ $this, 'allow_logins' ] );
	}
}

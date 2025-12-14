<?php
/**
 * Add compatibility for some plugins that functionality to permit visitors to a site to register, which should remain
 * on the primary domain.
 *
 * @package DarkMatter
 *
 * @since 2.0.0
 */

namespace DarkMatterPlugin\DomainMapping\Compat;

use DarkMatterPlugin\Registerable;

/**
 * Class AllowMappedLogins
 */
class AllowMappedLogins implements Registerable {

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
	 * Can class register.
	 *
	 * @return true
	 */
	public function can_register() {
		return true;
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

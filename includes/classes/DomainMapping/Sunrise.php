<?php
/**
 * Handles the logic for WordPress dropin plugin, `sunrise.php`.
 */

namespace DarkMatter\DomainMapping;

/**
 * Class Sunrise
 *
 * @since 3.0.0
 */
class Sunrise {
	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Set up various globals and constants which are used, either by Dark Matter itself or by other plugins and/or
	 * WordPress Core.
	 *
	 * @return void
	 */
	private function init() {
		/**
		 * Ensure `dark-matter` cache group is available globally.
		 */
		wp_cache_add_global_groups( 'dark-matter' );

		/**
		 * Define the `SUNRISE_LOADED`. Dark Matter Plugin does not make use of this but other plugins do in certain
		 * circumstances (i.e. Jetpack). So we define it for compatibility.
		 */
		if ( false === defined( 'SUNRISE_LOADED' ) ) {
			define( 'SUNRISE_LOADED', true );
		}

		/**
		 * Use to detect misconfiguration where the cookie domain is set.
		 */
		define( 'DARKMATTER_COOKIE_SET', ! defined( 'COOKIE_DOMAIN' ) );
	}
}

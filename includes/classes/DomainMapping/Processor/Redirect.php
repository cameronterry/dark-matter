<?php
/**
 * Processor for determining if a redirect is required.
 *
 * @since 3.0.0
 *
 * @package DarkMatterPlugin\Domain
 */

namespace DarkMatter\DomainMapping\Processor;

use DarkMatter\Interfaces\Registerable;

/**
 * Class Redirect
 *
 * @since 3.0.0
 */
class Redirect implements Registerable {
	/**
	 * Determine if the current request is something we consider for redirecting.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True to redirect, false otherwise.
	 */
	private function can_redirect() {
		return false;
	}

	/**
	 * Register hooks for this class.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register() {
		if ( $this->can_redirect() ) {
			// todo add_action( 'muplugins_loaded', 'darkmatter_maybe_redirect', 20 );
		}
	}
}

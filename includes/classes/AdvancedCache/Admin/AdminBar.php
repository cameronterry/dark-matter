<?php
/**
 * Provides some functionality on the admin bar.
 *
 * @package DarkMatter\AdvancedCache
 */
namespace DarkMatter\AdvancedCache\Admin;

use DarkMatter\AdvancedCache\Data\Request;
use DarkMatter\Interfaces\Registerable;

/**
 * Class AdminBar
 */
class AdminBar implements Registerable {
	/**
	 * Retrieves the URL for the current request.
	 *
	 * @return string URL, minus the protocol.
	 */
	private function get_url() {
		/**
		 * Retrieve the current request.
		 */
		global $wp;
		$url = get_home_url( null, $wp->request );

		/**
		 * Remote the protocol.
		 */
		$protocol = wp_parse_url( $url, PHP_URL_SCHEME );
		return trailingslashit( str_replace( "{$protocol}://", '', $url ) );
	}

	/**
	 * Handle hooks for the Admin Bar additions.
	 *
	 * @return void
	 */
	public function register() {
		// TODO: Implement register() method.
	}
}

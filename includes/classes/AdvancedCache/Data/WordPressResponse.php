<?php
/**
 * A version of the Response class which can be used as WordPress is generating a response.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\Data;

use DarkMatter\Interfaces\Registerable;

/**
 * Class WordPressResponse
 */
class WordPressResponse extends Response implements Registerable {
	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		ob_start( [ $this, 'set_body' ] );
		add_filter( 'status_header', [ $this, 'set_status_header' ], 10, 2 );
	}

	/**
	 * Capture the body for the WordPress request.
	 *
	 * @param string $output Output for the WordPress response.
	 * @return string
	 */
	public function set_body( $output = '' ) {
		$this->body    = $output;
		$this->headers = headers_list();

		/**
		 * Permit the override of the "is cacheable" value. This is useful for providing overrides as WordPress is
		 * dynamically constructing the request.
		 *
		 * @param bool              $is_cacheable True is cacheable, false otherwise, as determined by the default logic.
		 * @param WordPressResponse $response     The response object.
		 * @return bool True is cacheable. False otherwise.
		 */
		if ( apply_filters( 'darkmatter.advancedcache.response.is_cacheable', $this->is_cacheable(), $this ) ) {
			$this->cache();

			/**
			 * Indicate the request has been set dynamically.
			 */
			header( 'X-DarkMatter-Cache: DYNAMIC' );
		} else {
			/**
			 * Indicate the cache is another miss.
			 */
			header( 'X-DarkMatter-Cache: MISS' );
		}

		return $output;
	}

	/**
	 * Set the status header from the WordPress hook.
	 *
	 * @param string  $status_header HTTP status header.
	 * @param integer $status_code   HTTP status code.
	 * @return string
	 */
	public function set_status_header( $status_header = '', $status_code = 200 ) {
		$this->status_code = $status_code;
		return $status_header;
	}
}

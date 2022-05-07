<?php
/**
 * Processor for handling Requests.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\Processor;

use DarkMatter\AdvancedCache\Data\CacheEntry;
use DarkMatter\AdvancedCache\Data\WordPressResponse;

/**
 * Class Request
 *
 * @since 3.0.0
 */
class AdvancedCache {
	/**
	 * Request data.
	 *
	 * @var Requester
	 */
	private $request = null;

	/**
	 * Response.
	 *
	 * @var WordPressResponse
	 */
	private $response = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->request = new Requester( $_SERVER, $_COOKIE );

		if ( $this->request->is_cacheable() && ! $this->request->is_wp_logged_in ) {
			/**
			 * See if there is a "hit" on the cache entry. If so, then use this to serve the response and skip
			 * WordPress.
			 */
			$cache_entry = $this->request->cache_get();
			if ( ! empty( $cache_entry->headers ) ) {
				$this->hit( $cache_entry );
			}

			/**
			 * Here means there was no current cache entry. Therefore we do a "lookup" to generate a new cache entry
			 * with a response.
			 */
			$this->lookup();
		}
	}

	/**
	 * Handle a Cache "hit".
	 *
	 * @param CacheEntry $cache_entry
	 * @return void
	 */
	private function hit( $cache_entry ) {
		$headers = array_merge(
			$cache_entry->headers,
			[
				'X-DarkMatter-Cache' => 'HIT',
			]
		);

		$this->do_headers( $headers );
		die( $cache_entry->body );
	}

	/**
	 * Handle a response.
	 *
	 * @return void
	 */
	private function lookup() {
		$this->response = new WordPressResponse( $this->request->full_url );
		$this->response->register();
	}

	/**
	 * @param array $headers Headers to be part of the request.
	 * @return void
	 */
	public function do_headers( $headers = [] ) {
		foreach ( $headers as $name => $value ) {
			header( "{$name}: {$value}", true );
		}
	}
}

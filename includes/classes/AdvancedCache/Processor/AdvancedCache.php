<?php
/**
 * Processor for handling Requests.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\Processor;

use DarkMatter\AdvancedCache\Data\CacheEntry;
use DarkMatter\AdvancedCache\Data\Request;
use DarkMatter\AdvancedCache\Data\WordPressResponse;

/**
 * Class Request
 *
 * @since 3.0.0
 */
class AdvancedCache {
	/**
	 * Variant key, as determined by processing all the policies.
	 *
	 * @var string
	 */
	private $variant = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		$visitor = new Visitor( $_SERVER, $_COOKIE );
		$request = new Request( $visitor->full_url );

		// @todo Policies with a response ("maintenance page" example).
		// @todo Policy response when cache not found ("always serve from cache" example).
		// @todo Policy for WordPress session cookies.

		// @todo Instructions processor.
		// @todo Instruction to update content with supplied body.
		// @todo Instruction to find and replace a piece of content.
		// @todo Instruction to append content.

		if ( $visitor->is_cacheable() && ! $visitor->is_wp_logged_in ) {
			/**
			 * See if there is a "hit" on the cache entry. If so, then use this to serve the response and skip
			 * WordPress.
			 */
			$cache_entry = $request->get_variant( $this->variant );
			if ( ! empty( $cache_entry ) && ! $cache_entry->has_expired() ) {
				$this->hit( $cache_entry );
			}

			/**
			 * Here means there was no current cache entry. Therefore we do a "lookup" to generate a new cache entry
			 * with a response.
			 */
			$this->lookup( $request, $visitor->full_url );
		}
	}

	/**
	 * Handle the output of the headers for a cache hit.
	 *
	 * @param array $headers Headers to be part of the request.
	 * @return void
	 */
	public function do_headers( $headers = [] ) {
		foreach ( $headers as $name => $value ) {
			header( "{$name}: {$value}", true );
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
	 * @param Request $request  Request Data object.
	 * @param string  $full_url Full URL of the current request.
	 * @return void
	 */
	private function lookup( $request, $full_url ) {
		$response = new WordPressResponse( $full_url, $this->variant, $request );
		$response->register();
	}
}

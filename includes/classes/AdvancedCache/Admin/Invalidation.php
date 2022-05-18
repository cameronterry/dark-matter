<?php
/**
 * Invalidation mechanism when content is updated.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\Admin;

use DarkMatter\AdvancedCache\Data\Request;
use DarkMatter\Interfaces\Registerable;

/**
 * Class Invalidation
 */
class Invalidation implements Registerable {
	/**
	 * URLs to invalidate.
	 *
	 * @var array
	 */
	private $urls = [];

	/**
	 * Perform the invalidation.
	 *
	 * @return void
	 */
	public function invalidation() {
		if ( empty( $this->urls ) ) {
			return;
		}

		/**
		 * Prepare the URLs.
		 */
		$this->urls = array_map( [ $this, 'url' ], $this->urls );
		$expiry     = time() - 60;

		foreach ( $this->urls as $url ) {
			$request = new Request( $url );

			/**
			 * Invalidate the root.
			 */
			$variant = $request->get_variant();
			if ( empty( $variant ) ) {
				continue;
			}

			$variant->expiry = $expiry;
			$variant->save();

			/**
			 * Invalidate any variants.
			 */
			foreach ( $request->variants as $variant ) {
				$variant = $request->get_variant( $variant );
				$variant->expiry = $expiry;
				$variant->save();
			}
		}
	}

	/**
	 * Handle post invalidation.
	 *
	 * @param integer  $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @param bool     $update  New or update.
	 * @return void
	 */
	public function post( $post_id = 0, $post = null, $update = false ) {
		if ( empty( $post ) ) {
			$post = get_post( $post_id );
		}

		/**
		 * Ensure we have a Post object to work with it and that it isn't a "revision".
		 */
		if ( ! $post instanceof \WP_Post || 'revision' === $post->post_type ) {
			return;
		}

		/**
		 * Invalidate the post itself.
		 */
		$this->urls[] = get_permalink( $post );

		/**
		 * Invalidate the home page and the feed.
		 */
		$this->urls[] = home_url( '/' );
		$this->urls[] = home_url( '/feed/' );
	}

	/**
	 * Register hooks for actions and filters.
	 *
	 * @return void
	 */
	public function register() {
		/**
		 * Handle post creation and edits. We attempt to run this as late as possible to ensure plugins have a change to
		 * make changes before add entries to invalidate the cache.
		 */
		add_action( 'clean_post_cache', [ $this, 'post' ], 999, 1 );

		/**
		 * Prioritise invalidating cache entries before attempting to instantly cache again.
		 */
		add_action( 'shutdown', [ $this, 'invalidation' ], 10 );
	}

	/**
	 * Prepare the URL, namely by removing the protocol.
	 *
	 * @param string $url URL to prepare.
	 * @return string
	 */
	private function url( $url = '' ) {
		$protocol = wp_parse_url( $url, PHP_URL_SCHEME );
		return str_replace( "{$protocol}://", '', $url );
	}
}

<?php
/**
 * A version of the Response class which can be used as WordPress is generating a response.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\Data;

use DarkMatter\AdvancedCache\Processor\Instructions;
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

		add_action( 'shutdown', [ $this, 'shutdown' ] );
		add_action( 'template_redirect', [ $this, 'request_data' ] );

		add_filter( 'status_header', [ $this, 'set_status_header' ], 10, 2 );
	}

	/**
	 * Set the request data with information from WordPress.
	 *
	 * @return void
	 */
	public function request_data() {
		$data = [
			'home' => ( is_home() || is_front_page() ),
		];

		/**
		 * Translate the various queried objects into reusable data.
		 */
		$queried_object = get_queried_object();

		if ( is_a( $queried_object, '\WP_Post' ) ) {
			$data['archive']  = false;
			$data['singular'] = true;

			$data['post_author'] = $queried_object->post_author;
			$data['post_date']   = $queried_object->post_date;
			$data['post_id']     = $queried_object->ID;
			$data['post_name']   = $queried_object->post_name;
			$data['post_parent'] = $queried_object->post_parent;
			$data['post_type']   = $queried_object->post_type;
		} elseif ( is_a( $queried_object, '\WP_Term' ) ) {
			$data['archive']  = true;
			$data['singular'] = false;

			$data['term_id']     = $queried_object->term_id;
			$data['term_parent'] = $queried_object->parent;
			$data['term_slug']   = $queried_object->slug;
			$data['term_tax']    = $queried_object->taxonomy;
		} elseif ( is_a( $queried_object, 'WP_User' ) ) {
			$data['archive']  = true;
			$data['singular'] = false;
		}

		/**
		 * Add / remove data to be stored with the Advanced Cache > Request Data. This is useful for including custom
		 * data that is to be used with the advanced cache to determine variants.
		 *
		 * @param array $data Data to be stored with the Request cache.
		 */
		$this->request->data = apply_filters( 'darkmatter.advancedcache.request.data', $data );
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
			/**
			 * Adjust the time a response is cached for.
			 *
			 * @param integer           $ttl      Time To Live / expiry time (defaults to 5 minutes from time of execution).
			 * @param WordPressResponse $response Response object.
			 */
			$expiry = apply_filters( 'darkmatter.advancedcache.response.expiry', time() + 5 * MINUTE_IN_SECONDS, $this );
			$this->cache( $expiry );

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

		return Instructions::instance()->body( $output );
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

	/**
	 * Save the Request Data. This only fires on a dynamic request.
	 *
	 * @return void
	 */
	public function shutdown() {
		$this->request->save();
	}
}

<?php
/**
 * A Cache Entry, which contains all the headers and HTML for a response.
 *
 * @package DarkMatter
 */

namespace DarkMatter\AdvancedCache\Data;

/**
 * Class ResponseEntry
 *
 * @since 3.0.0
 */
class ResponseEntry implements \DarkMatter\Interfaces\Storeable {
	/**
	 * Request Body - can be plaintext, HTML, JSON, or XML.
	 *
	 * @var string
	 */
	public $body = '';

	/**
	 * Unix epoch of when the response entry expires. Zero (0) equates to "no expiry".
	 *
	 * @var int
	 */
	public $expiry = 0;

	/**
	 * Request Headers.
	 *
	 * @var array
	 */
	public $headers = [];

	/**
	 * Last time the Response Entry was modified.
	 *
	 * @var int
	 */
	public $lastmodified = 0;

	/**
	 * Response key based on the URL.
	 *
	 * @var string
	 */
	private $url_key = '';

	/**
	 * Constructor
	 *
	 * @param string $url         URL.
	 * @param string $variant_key Variant key.
	 */
	public function __construct( $url = '', $variant_key = '' ) {
		/**
		 * Convert the URL with md5() to make sure we have a straightforward string without the special chars.
		 */
		$this->url_key = md5( $url );
		if ( ! empty( $variant_key ) ) {
			$this->url_key = sprintf(
				'%s-%s',
				$this->url_key,
				$variant_key
			);
		}

		/**
		 * Attempt to retrieve the entry from Object Cache.
		 */
		$entry = wp_cache_get( $this->url_key, 'dark-matter-fpc-responses' );

		/**
		 * Parse the entry into JSON.
		 */
		if ( ! empty( $entry ) ) {
			$this->from_json( $entry );
		}
	}

	/**
	 * Populate this Cache Entry with the data from JSON.
	 *
	 * @param string $json JSON string to extract the data from.
	 * @return void
	 */
	public function from_json( $json = '' ) {
		if ( empty( $json ) ) {
			return;
		}

		$obj = json_decode( $json );

		if ( isset( $obj->headers ) && is_array( $obj->headers ) ) {
			$this->headers = $obj->headers;
		}

		if ( isset( $obj->body ) ) {
			$this->body = $obj->body;
		}

		if ( isset( $obj->expiry ) ) {
			$this->expiry = $obj->expiry;
		}

		if ( isset( $obj->lastmodified ) ) {
			$this->lastmodified = $obj->lastmodified;
		}
	}

	/**
	 * Checks if the ResponseEntry has expired.
	 *
	 * @return bool True if expired. False otherwise.
	 */
	public function has_expired() {
		/**
		 * Response Entry has expired check. Permits the expiry to be overridden.
		 *
		 * @param bool          $has_expired True if the Response Entry has expired. False otherwise.
		 * @param ResponseEntry $entry       ResponseEntry.
		 * @return bool True if expired, false otherwise.
		 */
		return apply_filters( 'darkmatter.advancedcache.response.has_expired', ( 0 !== $this->expiry && $this->expiry <= time() ), $this );
	}

	/**
	 * Convert the Cache Entry to JSON.
	 *
	 * @return string JSON string of the Cache Entry.
	 */
	public function to_json() {
		return wp_json_encode(
			[
				'headers'      => $this->headers,
				'body'         => $this->body,
				'expiry'       => $this->expiry,
				'lastmodified' => $this->lastmodified,
			]
		);
	}

	/**
	 * Save the cache entry.
	 *
	 * @return string|false URL key on success. False otherwise.
	 */
	public function save() {
		/**
		 * Update the Last Modified property.
		 */
		$this->lastmodified = time();

		/**
		 * Attempt to cache the entry.
		 */
		if ( wp_cache_set( $this->url_key, $this->to_json(), 'dark-matter-fpc-responses' ) ) {
			return $this->url_key;
		}

		return false;
	}
}

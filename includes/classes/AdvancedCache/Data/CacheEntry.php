<?php
/**
 * A Cache Entry, which contains all the headers and HTML for a response.
 *
 * @package DarkMatter
 */
namespace DarkMatter\AdvancedCache\Data;

/**
 * Class CacheEntry
 *
 * @since 3.0.0
 */
class CacheEntry implements \DarkMatter\Interfaces\Storeable {
	/**
	 * Request Body - can be plaintext, HTML, JSON, or XML.
	 *
	 * @var string
	 */
	public $body = '';

	/**
	 * Unix epoch of when the cache entry expires. Zero (0) equates to "no expiry".
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
	 * Last time the Cache Entry was modified.
	 *
	 * @var int
	 */
	public $lastmodified = 0;

	/**
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
		$entry = wp_cache_get( $this->url_key, 'dark-matter-fpc-cacheentries' );

		/**
		 * Parse the entry into JSON.
		 */
		if ( ! empty( $entry ) ) {
			$this->from_json( $entry );
		}
	}

	/**
	 * @inheritDoc
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
	 * Checks if the CacheEntry has expired.
	 *
	 * @return bool True if expired. False otherwise.
	 */
	public function has_expired() {
		return ( 0 !== $this->expiry && $this->expiry <= time() );
	}

	/**
     * @inheritDoc
     */
    public function to_json() {
        return wp_json_encode( [
			'headers'      => $this->headers,
			'body'         => $this->body,
			'expiry'       => $this->expiry,
			'lastmodified' => $this->lastmodified,
		] );
    }

	/**
	 * @inheritdoc
	 */
	public function save() {
		/**
		 * Update the Last Modified property.
		 */
		$this->lastmodified = time();

		/**
		 * Attempt to cache the entry.
		 */
		if ( wp_cache_set( $this->url_key, $this->to_json(), 'dark-matter-fpc-cacheentries' ) ) {
			return $this->url_key;
		}

		return false;
	}
}

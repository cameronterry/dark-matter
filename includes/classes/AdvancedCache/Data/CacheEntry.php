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
	private $expiry = 0;

	/**
	 * Request Headers.
	 *
	 * @var array
	 */
	public $headers = [];

	/**
	 * @var string
	 */
	private $url_key = '';

	/**
	 * Constructor
	 */
	public function __construct( $url = '' ) {
		/**
		 * Convert the URL with md5() to make sure we have a straightforward string without the special chars.
		 */
		$this->url_key = md5( $url );

		/**
		 * Attempt to retrieve the entry from Object Cache.
		 */
		$entry = wp_cache_get( md5( $this->url_key ), 'darkmatter-fpc-cacheentries' );

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
    public function to_json() {
        return wp_json_encode( (object) [
			'headers' => $this->headers,
			'body'    => $this->body,
		] );
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
    }

	/**
	 * @inheritdoc
	 */
	public function save() {
		if ( wp_cache_set( $this->url_key, $this->to_json(), 'darkmatter-fpc-cacheentries', $this->expiry ) ) {
			return $this->url_key;
		}

		return false;
	}

	/**
	 * Set when the Cache Entry is to expiry.
	 *
	 * @param integer $time Unix epoch of the expiry time for the entry. Zero equates to no expiry.
	 * @return void
	 */
	public function set_expiry( $time = 0 ) {
		/**
		 * Only set an Expiry if it's in the future or zero (0 - no expiry).
		 */
		if ( 0 !== $time && time() > $time ) {
			return;
		}

		$this->expiry = $time;
	}
}

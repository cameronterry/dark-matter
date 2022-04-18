<?php
/**
 * Standardise the response for use in Advanced Cache.
 */

namespace DarkMatter\AdvancedCache\Data;

use DarkMatter\Interfaces\Registerable;

/**
 * Class Response
 */
class Response implements Registerable {
	/**
	 * Body as string. This is most commonly HTML but can also be JSON, XML (i.e. RSS), or plaintext.
	 *
	 * @var string
	 */
	public $body = '';

	/**
	 * Full URl.
	 *
	 * @var string
	 */
	public $full_url = '';

	/**
	 * @var array
	 */
	public $headers = [];

	/**
	 * Status Code
	 *
	 * @var int
	 */
	public $status_code = 200;

	/**
	 * Constructor
	 *
	 * @param string $full_url Full URL which created the response.
	 * @param string $body     Body of the response (usually HTML).
	 */
	public function __construct( $full_url = '', $body = '' ) {
		$this->body     = $body;
		$this->full_url = $full_url;
		$this->headers  = headers_list();
	}

	/**
	 * Is the response cacheable.
	 *
	 * @return bool True if response is cacheable. False otherwise.
	 */
	public function is_cacheable() {
		/**
		 * Do not cache errors.
		 */
		if ( 5 === intval( $this->status_code / 100 ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @return bool|string Cache key when stored successfully. False otherwise.
	 */
	public function cache() {
		/**
		 * Create a new CacheEntry object.
		 */
		$entry = new CacheEntry( $this->full_url );

		/**
		 * Set the body and headers.
		 */
		$entry->body    = $this->body;
		$entry->headers = $this->headers;

		return $entry->save();
	}

	/**
	 * Register hooks.
	 *
	 * Note: used for processing a live request within WordPress. Can be ignored otherwise.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'status_header', [ $this, 'set_status_header' ], 10, 2 );
	}

	/**
	 * Set the status header from the WordPress hook.
	 *
	 * Note: used for processing a live request within WordPress. Can be ignored otherwise.
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

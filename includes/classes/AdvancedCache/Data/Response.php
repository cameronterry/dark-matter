<?php
/**
 * Standardise the response for use in Advanced Cache.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\Data;

/**
 * Class Response
 */
class Response {
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
	 * Request Data object.
	 *
	 * @var Request
	 */
	protected $request = null;

	/**
	 * Status Code
	 *
	 * @var int
	 */
	public $status_code = 200;

	/**
	 * Constructor
	 *
	 * @param string  $full_url Full URL which created the response.
	 * @param string  $body     Body of the response (usually HTML).
	 * @param Request $request  Request Data object.
	 */
	public function __construct( $full_url = '', $body = '', $request = null ) {
		$this->body     = $body;
		$this->full_url = $full_url;
		$this->headers  = headers_list();
		$this->request  = $request;
	}

	/**
	 * Is the response cacheable.
	 *
	 * @return bool True if response is cacheable. False otherwise.
	 */
	public function is_cacheable() {
		/**
		 * Request determination of is cacheable takes precedence.
		 */
		if ( ! $this->request->is_cacheable ) {
			return false;
		}

		/**
		 * Do not cache errors.
		 */
		if ( 5 === intval( $this->status_code / 100 ) ) {
			return false;
		}

		/**
		 * Check for a header - `X-DarkMatter-Set` - which can prevent a response being cached.
		 *
		 * The reason for using a header is in case the full page cache handling is offloaded to NGINX for response.
		 */
		if ( ! empty( $this->headers ) && isset( $this->headers['X-DarkMatter-Set'] ) && 'skip' === $this->headers['X-DarkMatter-Set'] ) {
			/**
			 * Remove the header from the response.
			 */
			header_remove( 'X-DarkMatter-Set' );

			/**
			 * Say this response isn't cacheable.
			 */
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

		/**
		 * Cache the output and store the variant key with the Request Data.
		 */
		$variant_key = $entry->save();
		$this->request->variants[ $variant_key ] = true;

		return $variant_key;
	}
}

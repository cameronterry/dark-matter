<?php
/**
 * Request
 *
 * @package DarkMatter\AdvancedCache
 */
namespace DarkMatter\AdvancedCache\Data;

use DarkMatter\Interfaces\Storeable;

/**
 * Class Request
 */
class Request implements Storeable {
	/**
	 * @var string
	 */
	public $cache_key = '';

	/**
	 * Request data, usually provided by the CMS.
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Expiry as a Unix epoch. Defaults to -1, which equates to "infinite" / "until cleared".
	 *
	 * @var int
	 */
	public $expiry = -1;

	/**
	 * Full URL of the Request.
	 *
	 * @var string
	 */
	public $full_url = '';

	/**
	 * Note on the latest instruction received and processed.
	 *
	 * @var int
	 */
	public $instruction = 0;

	/**
	 * Is the request cacheable.
	 *
	 * @var bool
	 */
	public $is_cacheable = true;

	/**
	 * Versions of the request based on policies.
	 *
	 * @var array
	 */
	public $variants = [];

	/**
	 * Constructor.
	 *
	 * @param string $full_url Full URL.
	 */
	public function __construct( $full_url = '' ) {
		$this->cache_key = md5( $full_url );
		$this->full_url  = $full_url;

		/**
		 * Retrieve from cache if possible.
		 */
		$cache = wp_cache_get( $this->cache_key, 'dark-matter-fpc-request' );
		if ( ! empty( $cache ) ) {
			$this->from_json( $cache );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function to_json() {
		return wp_json_encode(
			[
				'data'        => $this->data,
				'expiry'      => $this->expiry,
				'instruction' => $this->instruction,
				'variants'    => $this->variants,
			]
		);
	}

	/**
	 * @inheritDoc
	 */
	public function from_json( $json = '' ) {
		if ( empty( $json ) ) {
			return;
		}

		$obj = json_decode( $json );

		$this->data        = $obj->data;
		$this->expiry      = $obj->expiry;
		$this->instruction = $obj->instruction;
		$this->variants    = $obj->variants;
	}

	/**
	 * Retrieve a cache entry for the Request.
	 *
	 * @param string $key Key of variant to request. If provided, then `null` can be returned to denote a variant is not found.
	 * @return CacheEntry|null CacheEntry on success. Null otherwise.
	 */
	public function get_variant( $key = '' ) {
		if ( ! empty( $key ) && ! array_key_exists( $key, $this->variants ) ) {
			return null;
		}

		/**
		 * Find a cache entry and ensure it does exist. Headers and other properties will be empty if there is not an
		 * entry stored in cache.
		 */
		$cache_entry = new CacheEntry( $this->full_url, $key );
		if ( ! empty( $cache_entry->headers ) ) {
			return $cache_entry;
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function save() {
		return wp_cache_set( $this->cache_key, $this->to_json(), 'dark-matter-fpc-request' );
	}
}

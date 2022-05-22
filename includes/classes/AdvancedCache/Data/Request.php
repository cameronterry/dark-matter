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
	 * Cache key.
	 *
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
		global $darkmatter_cache_storage;

		$cache = $darkmatter_cache_storage->get( $this->cache_key, 'dark-matter-fpc-request', 'request' );
		if ( ! empty( $cache ) ) {
			$this->from_json( $cache );
		}
	}

	/**
	 * Delete the Request object.
	 *
	 * @return bool
	 */
	public function delete() {
		/**
		 * Delete the default variant.
		 */
		$variant = $this->get_variant();
		if ( $variant instanceof ResponseEntry ) {
			$variant->delete();
		}

		/**
		 * Delete all other variants.
		 */
		foreach ( $this->variants as $variant_key => $value ) {
			$variant = $this->get_variant( $variant_key );

			if ( $variant instanceof ResponseEntry ) {
				$variant->delete();
			}
		}

		/**
		 * Delete the Request object itself.
		 */
		global $darkmatter_cache_storage;
		return $darkmatter_cache_storage->delete( $this->cache_key, 'dark-matter-fpc-request', 'request' );
	}

	/**
	 * Convert the Request to JSON.
	 *
	 * @return string JSON string of the Request.
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
	 * Populate this Request with the data from JSON.
	 *
	 * @param string $json JSON string to extract the data from.
	 * @return void
	 */
	public function from_json( $json = '' ) {
		if ( empty( $json ) ) {
			return;
		}

		$obj = json_decode( $json );

		$this->data        = $obj->data;
		$this->expiry      = $obj->expiry;
		$this->instruction = $obj->instruction;
		$this->variants    = (array) $obj->variants;
	}

	/**
	 * Retrieve a cache entry for the Request.
	 *
	 * @param string $key Key of variant to request. If provided, then `null` can be returned to denote a variant is not found.
	 *
	 * @return ResponseEntry|null ResponseEntry on success. Null otherwise.
	 */
	public function get_variant( $key = '' ) {
		if ( ! empty( $key ) ) {
			$full_key = sprintf(
				'%1$s-%2$s',
				$this->cache_key,
				$key
			);

			if ( ! array_key_exists( $full_key, $this->variants ) ) {
				return null;
			}
		}

		/**
		 * Find a cache entry and ensure it does exist. Headers and other properties will be empty if there is not an
		 * entry stored in cache.
		 */
		$cache_entry = new ResponseEntry( $this->full_url, $key );
		if ( ! empty( $cache_entry->headers ) ) {
			return $cache_entry;
		}

		return null;
	}

	/**
	 * Save the Request object.
	 *
	 * @return bool True on success. False otherwise.
	 */
	public function save() {
		global $darkmatter_cache_storage;
		return $darkmatter_cache_storage->set( $this->cache_key, $this->to_json(), 'dark-matter-fpc-request', 0, 'request' );
	}
}

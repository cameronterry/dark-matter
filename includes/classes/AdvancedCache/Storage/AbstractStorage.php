<?php
/**
 * Foundational class for implementing a storage for Advanced Cache.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\Storage;

/**
 * Class AbstractStorage
 */
abstract class AbstractStorage {
	/**
	 * Delete a value in cache.
	 *
	 * @param string $key   The key under which to store the value.
	 * @param string $group The group value appended to the $key.
	 * @return bool True on success. False otherwise.
	 */
	abstract public function delete( $key, $group ) : bool;

	/**
	 * Retrieve a value in cache.
	 *
	 * @param string $key   The key under which to store the value.
	 * @param string $group The group value appended to the $key.
	 * @return mixed Cached value.
	 */
	abstract public function get( $key, $group );

	/**
	 * Add / update value in cache.
	 *
	 * @param string $key        The key under which to store the value.
	 * @param mixed  $value      The value to store.
	 * @param string $group      The group value appended to the $key.
	 * @param int    $expiration The expiration time, defaults to 0 (infinite / until modified).
	 * @return bool True on success. False otherwise.
	 */
	abstract public function set( $key, $value, $group, $expiration = 0 );
}

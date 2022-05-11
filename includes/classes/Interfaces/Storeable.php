<?php
/**
 * An interface for standardising the classes which are to be stored.
 *
 * @package DarkMatter
 */

namespace DarkMatter\Interfaces;

/**
 * Interface Storeable.
 */
interface Storeable {
	/**
	 * Converts the current object to a JSON string.
	 *
	 * @return string
	 */
	public function to_json();

	/**
	 * Converts the provided JSON into the current object (if valid).
	 *
	 * @param string $json JSON as string.
	 * @return mixed
	 */
	public function from_json( $json = '' );

	/**
	 * Handle the saving of the object to the store.
	 *
	 * @return bool
	 */
	public function save();
}

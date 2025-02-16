<?php
/**
 * Useful interface for making registerable classes consistent.
 *
 * @package DarkMatter
 */

namespace DarkMatter\Interfaces;

/**
 * Interface Registerable
 *
 * @since 3.0.0
 */
interface Registerable {

	/**
	 * Can the class be registered.
	 *
	 * @return bool
	 */
	public function can_register();

	/**
	 * Register method for connecting with actions and filters.
	 *
	 * @return void
	 */
	public function register();
}

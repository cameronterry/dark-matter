<?php
/**
 * Useful interface for making registerable classes consistent.
 *
 * @package DarkMatter
 */

namespace DarkMatter;

/**
 * Interface Registerable
 *
 * @since 3.0.0
 */
interface Registerable {
	/**
	 * Register method for connecting with actions and filters.
	 *
	 * @return void
	 */
	public function register();
}

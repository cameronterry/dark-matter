<?php
/**
 * Standardised interface for loading classes within this plugin.
 *
 * @package DarkMatterPlugin
 */

namespace DarkMatterPlugin;

/**
 * Interface Registerable
 */
interface Registerable {

	/**
	 * Determines if this class can be registered.
	 *
	 * @return bool
	 */
	public function can_register();

	/**
	 * Hooks for the actions and filters used by the inheriting class.
	 *
	 * @return void
	 */
	public function register();
}

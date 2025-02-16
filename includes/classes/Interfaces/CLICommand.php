<?php
/**
 * Helper for creating CLI commands.
 *
 * @package DarkMatterPlugin
 */


namespace DarkMatter\Interfaces;

/**
 * Class CLICommand
 *
 * @since 3.0.0
 */
interface CLICommand {

	/**
	 * Can the class be registered.
	 *
	 * @return bool
	 */
	public static function can_register();

	/**
	 * Register method for connecting with actions and filters.
	 *
	 * @return void
	 */
	public static function register();
}

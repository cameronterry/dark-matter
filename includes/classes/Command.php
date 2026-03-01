<?php
/**
 * Standardise interface for the plugin's CLI Commands.
 *
 * Note: the use of `static` methods is deliberate, to prevent these methods polluting the subcommands of the class when
 * used with WP CLI.
 *
 * @package DarkMatterPlugin
 */

namespace DarkMatterPlugin;

/**
 * Abstract Class Command
 */
interface Command {

	/**
	 * Add the command be registered.
	 *
	 * @return bool
	 */
	public static function can_register();

	/**
	 * Register the command(s) for use.
	 *
	 * Note: use the following to register the command, substituting placeholders within square braces ([]).
	 *
	 * ```shell
	 * \WP_CLI::add_command( '[namespace] [command]', self::class );
	 * ```
	 *
	 * @return void
	 */
	public static function register();
}

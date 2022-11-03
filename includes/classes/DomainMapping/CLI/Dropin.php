<?php
/**
 * CLI for managing the Domain Mapping dropin (sunrise.php).
 *
 * @since 2.0.0
 *
 * @package DarkMatter\DomainMapping
 */

namespace DarkMatter\DomainMapping\CLI;

use DarkMatter\DomainMapping\Admin\HealthChecks;
use WP_CLI;
use WP_CLI_Command;

/**
 * Class Dropin
 *
 * Previously called `DarkMatter_Dropin_CLI`.
 *
 * @since 2.0.0
 */
class Dropin extends WP_CLI_Command {
	/**
	 * Helper command to see if the Sunrise dropin plugin within Dark Matter is
	 * the same version as in use on the current WordPress installation.
	 *
	 * ### Examples
	 * Check to see if the Sunrise dropin is the latest version.
	 *
	 *      wp darkmatter dropin check
	 *
	 * @since 2.0.0
	 */
	public function check() {
		$health_check = new HealthChecks();
		if ( $health_check->is_dropin_latest() ) {
			WP_CLI::success( __( 'Current Sunrise dropin matches the Sunrise within Dark Matter plugin.', 'dark-matter' ) );
			return;
		}

		WP_CLI::error( __( 'Sunrise dropin does not match the Sunrise within Dark Matter plugin. Consider using the "update" command to correct this issue.', 'dark-matter' ) );
	}

	/**
	 * Include this CLI amongst the others.
	 *
	 * @return void
	 */
	public static function define() {
		WP_CLI::add_command( 'darkmatter dropin', self::class );
	}

	/**
	 * Upgrade the Sunrise dropin plugin to the latest version within the Dark
	 * Matter plugin.
	 *
	 * ### OPTIONS
	 *
	 * [--force]
	 * : Force Dark Matter to override and update Sunrise dropin if a file
	 * already exists.
	 *
	 * ### EXAMPLES
	 * Install the Sunrise dropin plugin for new installations.
	 *
	 *      wp darkmatter dropin update
	 *
	 * Update the Sunrise dropin plugin, even if a file is already present.
	 *
	 *      wp darkmatter dropin update --force
	 *
	 * @since 2.0.0
	 *
	 * @param array $args CLI args.
	 * @param array $assoc_args CLI args maintaining the flag names from the terminal.
	 */
	public function update( $args, $assoc_args ) {
		$destination = WP_CONTENT_DIR . '/sunrise.php';
		$source      = DM_PATH . '/includes/dropins/sunrise.php';

		$opts = wp_parse_args(
			$assoc_args,
			[
				'force' => false,
			]
		);

        // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_is_writable
		if ( false === is_writable( WP_CONTENT_DIR ) ) {
			WP_CLI::error( __( 'The /wp-content/ directory needs to be writable by the current user in order to update.', 'dark-matter' ) );
		}

		if ( false === $opts['force'] && file_exists( $destination ) ) {
			WP_CLI::error( __( 'Sunrise is already present. Use the --force flag to override.', 'dark-matter' ) );
		}

		if ( false === is_readable( $source ) ) {
			WP_CLI::error( __( 'Cannot read the Sunrise dropin within the Dark Matter plugin folder.', 'dark-matter' ) );
		}

		if ( false === file_exists( $source ) ) {
			WP_CLI::error( __( 'Sunrise dropin within the Dark Matter plugin is missing.', 'dark-matter' ) );
		}

        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( @copy( $source, $destination ) ) {
			WP_CLI::success( __( 'Updated the Sunrise dropin to the latest version.', 'dark-matter' ) );
		} else {
			WP_CLI::error( __( 'Unknown error occurred preventing the update of Sunrise dropin.', 'dark-matter' ) );
		}
	}
}

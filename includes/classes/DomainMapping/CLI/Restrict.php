<?php
/**
 * Command for restricting (or reserving) domains from use.
 *
 * @package DarkMatter
 * @since 2.0.0
 */

namespace DarkMatterPlugin\DomainMapping\CLI;

use DarkMatterPlugin\Command;
use WP_CLI;

/**
 * Class Restrict
 *
 * @since 2.0.0
 */
class Restrict implements Command {

	/**
	 * Add a domain to the restrict for the WordPress Network.
	 *
	 * ### OPTIONS
	 *
	 * <domain>
	 * : The domain you wish to add to the restrict list.
	 *
	 * ### EXAMPLES
	 * Add a domain to the restrict list.
	 *
	 *      wp darkmatter restrict add www.example.com
	 *
	 * @since 2.0.0
	 *
	 * @param array $args CLI args.
	 * @param array $assoc_args CLI args maintaining the flag names from the terminal.
	 */
	public function add( $args, $assoc_args ) {
		if ( empty( $args[0] ) ) {
			WP_CLI::error( __( 'Please include a fully qualified domain name to be added.', 'dark-matter' ) );
		}

		$fqdn = $args[0];

		$restricted = \DarkMatter_Restrict::instance();
		$result     = $restricted->add( $fqdn );

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}

		WP_CLI::success( $fqdn . __( ': is now restricted.', 'dark-matter' ) );
	}

	/**
	 * Register the Restrict class.
	 *
	 * @return bool
	 */
	public static function can_register() {
		return true;
	}

	/**
	 * Retrieve a list of all Restricted domains for the Network.
	 *
	 * ### OPTIONS
	 *
	 * [--format]
	 * : Determine which format that should be returned. Defaults to "table" and
	 * accepts "ids", "json", "csv", "yaml", and "count".
	 *
	 * ### EXAMPLES
	 * List all domains for the Network.
	 *
	 *      wp darkmatter restrict list
	 *
	 * List all domains for the Network in JSON format.
	 *
	 *      wp darkmatter restrict list --format=json
	 *
	 * Return all restricted domains for the Network as a string separated by
	 * spaces.
	 *
	 *      wp darkmatter restrict list --format=ids
	 *
	 * @since 2.0.0
	 *
	 * @subcommand list
	 *
	 * @param array $args CLI args.
	 * @param array $assoc_args CLI args maintaining the flag names from the terminal.
	 */
	public function _list( $args, $assoc_args ) {
		/**
		 * Handle and validate the format flag if provided.
		 */
		$opts = wp_parse_args(
			$assoc_args,
			[
				'format' => 'table',
			]
		);

		if ( ! in_array( $opts['format'], array( 'ids', 'table', 'json', 'csv', 'yaml', 'count' ) ) ) {
			$opts['format'] = 'table';
		}

		$db = \DarkMatter_Restrict::instance();

		$restricted = $db->get();

		/**
		 * Only format the return array if "ids" is not specified.
		 */
		if ( 'ids' !== $opts['format'] ) {
			$restricted = array_map(
				function ( $domain ) {
					return array(
						'F.Q.D.N.' => $domain,
					);
				},
				$restricted
			);
		}

		WP_CLI\Utils\format_items(
			$opts['format'],
			$restricted,
			[
				'F.Q.D.N.',
			]
		);
	}

	/**
	 * Handle the registration of the Restrict CLI.
	 *
	 * @return void
	 */
	public static function register() {
		WP_CLI::add_command( 'darkmatter restrict', self::class );
	}

	/**
	 * Remove a domain to the restrict for the WordPress Network.
	 *
	 * ### OPTIONS
	 *
	 * <domain>
	 * : The domain you wish to remove to the restrict list.
	 *
	 * ### EXAMPLES
	 * Remove a domain to the restrict list.
	 *
	 *      wp darkmatter restrict remove www.example.com
	 *
	 * @since 2.0.0
	 *
	 * @param array $args CLI args.
	 * @param array $assoc_args CLI args maintaining the flag names from the terminal.
	 */
	public function remove( $args, $assoc_args ) {
		if ( empty( $args[0] ) ) {
			WP_CLI::error( __( 'Please include a fully qualified domain name to be added.', 'dark-matter' ) );
		}

		$fqdn = $args[0];

		$restricted = \DarkMatter_Restrict::instance();
		$result     = $restricted->delete( $fqdn );

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}

		WP_CLI::success( $fqdn . __( ': is no longer restricted.', 'dark-matter' ) );
	}
}

<?php
/**
 * CLI for managing restricted domains.
 *
 * @since 2.0.0
 *
 * @package DarkMatterPlugin\DomainMapping
 */

namespace DarkMatter\DomainMapping\CLI;

use \DarkMatter\DomainMapping\Manager;
use WP_CLI;
use WP_CLI_Command;

/**
 * Class Restricted
 *
 * Previously called `DarkMatter_Restrict_CLI`.
 *
 * @since 2.0.0
 */
class Restricted extends WP_CLI_Command {
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

		$restricted = Manager\Restricted::instance();
		$result     = $restricted->add( $fqdn );

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}

		WP_CLI::success( $fqdn . __( ': is now restricted.', 'dark-matter' ) );
	}

	/**
	 * Include this CLI amongst the others.
	 *
	 * @return void
	 */
	public static function define() {
		WP_CLI::add_command( 'darkmatter restrict', self::class );
	}

	/**
	 * Retrieve a list of all Restricted domains for the Network.
	 *
	 * ### OPTIONS
	 *
	 * * [--format]
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
	 * @param array $args CLI args.
	 * @param array $assoc_args CLI args maintaining the flag names from the terminal.
	 */
	public function list( $args, $assoc_args ) {
		/**
		 * Handle and validate the format flag if provided.
		 */
		$opts = wp_parse_args(
			$assoc_args,
			[
				'format' => 'table',
			]
		);

		if ( ! in_array( $opts['format'], [ 'ids', 'table', 'json', 'csv', 'yaml', 'count' ] ) ) {
			$opts['format'] = 'table';
		}

		$db = Manager\Restricted::instance();

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

		$restricted = Manager\Restricted::instance();
		$result     = $restricted->delete( $fqdn );

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}

		WP_CLI::success( $fqdn . __( ': is no longer restricted.', 'dark-matter' ) );
	}
}

<?php
/**
 * Main CLI controls for the Advanced Cache.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\CLI;

use DarkMatter\AdvancedCache\Data\Request;
use DarkMatter\AdvancedCache\Data\ResponseEntry;
use WP_CLI;
use WP_CLI_Command;

/**
 * Class AdvancedCache
 */
class AdvancedCache extends WP_CLI_Command {
	/**
	 * Used to add this class to the available CLI commands.
	 *
	 * @return void
	 */
	public static function define() {
		WP_CLI::add_command( 'darkmatter cache', self::class );
	}

	/**
	 * Delete response from cache.
	 *
	 * ### OPTIONS
	 *
	 * <url>
	 * : Full URL.
	 *
	 * [<variant_key>]
	 * : Specify a specific variant.
	 *
	 * @param $args
	 * @param $assoc_args
	 * @throws Exception
	 */
	public function delete( $args, $assoc_args ) {
		/**
		 * Check we actually got a URL.
		 */
		if ( empty( $args[0] ) ) {
			WP_CLI::error( __( 'Please provide a URL.', 'dark-matter' ) );
		}

		if ( ! filter_var( $args[0], FILTER_SANITIZE_URL ) ) {
			WP_CLI::error( __( 'URL is invalid.', 'dark-matter' ) );
		}

		$variant_key = '';
		if ( ! empty( $args[1] ) ) {
			$variant_key = $args[1];
		}

		/**
		 * Remove the URL scheme.
		 */
		$protocol = wp_parse_url( $args[0], PHP_URL_SCHEME );
		$url      = str_replace( $protocol, '', $args[0] );

		$request  = new Request( $url );
		$response = $request->get_variant( $variant_key );

		if ( $response instanceof ResponseEntry && $response->delete() ) {
			WP_CLI::success( __( 'Response entry has been deleted from cache.', 'dark-matter' ) );
		} else {
			WP_CLI::error( __( 'Could not delete response entry.', 'dark-matter' ) );
		}
	}

	/**
	 * Get information on a particular request / response entry or entries.
	 *
	 * ### OPTIONS
	 *
	 * <url>
	 * : Full URL.
	 *
	 * [<variant_key>]
	 * : Specify a specific variant.
	 *
	 * @param $args
	 * @param $assoc_args
	 * @throws Exception
	 */
	public function info( $args, $assoc_args ) {

	}

	/**
	 * Flush the request and the corresponding response entries.
	 *
	 * ### OPTIONS
	 *
	 * <url>
	 * : Full URL.
	 *
	 * @param $args
	 * @param $assoc_args
	 * @throws Exception
	 */
	public function flush( $args, $assoc_args ) {

	}

	/**
	 * Set a response entry.
	 *
	 * ### OPTIONS
	 *
	 * <url>
	 * : Full URL.
	 *
	 * [<variant_key>]
	 * : Specify a specific variant.
	 *
	 * @param $args
	 * @param $assoc_args
	 * @throws Exception
	 */
	public function set( $args, $assoc_args ) {

	}
}

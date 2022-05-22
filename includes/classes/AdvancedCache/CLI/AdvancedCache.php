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
	 * Constructor.
	 */
	public function __construct() {
		/**
		 * Define a global for the cache storage.
		 */
		global $darkmatter_cache_storage;

		/**
		 * Attempt to load any customisations.
		 */
		if ( defined( 'DARKMATTER_ADVANCEDCACHE_CUSTOM' ) ) {
			$darkmatter_customisations_path = trailingslashit( DARKMATTER_ADVANCEDCACHE_CUSTOM );
		} else {
			$darkmatter_customisations_path = dirname( __FILE__ ) . '/mu-plugins/advanced-cache/dark-matter.php';
		}

		if ( file_exists( $darkmatter_customisations_path ) ) {
			require_once $darkmatter_customisations_path;
		}

		/**
		 * Check if the global has been setup and if it is inheriting `AbstractStorage`. If not, then use the default with
		 * the plugin which utilises the Object Cache API.
		 */
		if ( empty( $darkmatter_cache_storage ) || ! $darkmatter_cache_storage instanceof \DarkMatter\AdvancedCache\Storage\AbstractStorage ) {
			$darkmatter_cache_storage = new \DarkMatter\AdvancedCache\Storage\WPCacheStorage();
		}
	}

	/**
	 * Check and prepare the URL.
	 *
	 * @param string $url URL to be checked.
	 * @return string URL that can be used to retrieve a Request object.
	 */
	private function check_url( $url = '' ) {
		/**
		 * Check we actually got a URL.
		 */
		if ( empty( $url ) ) {
			WP_CLI::error( __( 'Please provide a URL.', 'dark-matter' ) );
		}

		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			WP_CLI::error( __( 'URL is invalid.', 'dark-matter' ) );
		}

		/**
		 * Remove the URL scheme.
		 */
		$protocol = wp_parse_url( $url, PHP_URL_SCHEME );
		return str_replace( $protocol . '://', '', $url );
	}

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
		$url = $this->check_url( $args[0] );

		$variant_key = '';
		if ( ! empty( $args[1] ) ) {
			$variant_key = $args[1];
		}

		$request  = new Request( $url );
		$response = $request->get_variant( $variant_key );

		if ( $response instanceof ResponseEntry && $response->delete() ) {
			WP_CLI::success( __( 'Response entry has been deleted from cache.', 'dark-matter' ) );
		} else {
			WP_CLI::error( __( 'Response entry could not be found. It is possibly already deleted.', 'dark-matter' ) );
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

<?php
/**
 * Plugin Update
 *
 * @package DarkMatter
 *
 * @since 2.2.0
 */

/**
 * Class DM_PluginUpdate
 *
 * @since 2.2.0
 */
class DM_PluginUpdate {
	/**
	 * Cache key for the API response from WP Update Server.
	 *
	 * @since 2.2.0
	 *
	 * @var string
	 */
	private $cache_key = 'dark_matter_update_api_response';

	/**
	 * Stores the plugin slug.
	 *
	 * @since 2.2.0
	 *
	 * @var string
	 */
	private $plugin_slug = 'dark-matter/dark-matter.php';

	/**
	 * Constructor
	 *
	 * @since 2.2.0
	 */
	public function __construct() {
		add_filter( 'plugins_api', [ $this, 'plugin_info' ], 20, 3 );
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'push_update' ], 10, 1 );
	}

	/**
	 * Check to ensure that the currently installed version of the plugin needs to be updated.
	 *
	 * @since 2.2.0
	 *
	 * @param object $data Plugin information as an object.
	 * @return bool Returns true if update is required. False otherwise.
	 */
	private function needs_update( $data = null ) {
		/**
		 * No data, no update. Not strictly true but suffices for this logic.
		 */
		if ( empty( $data ) ) {
			return false;
		}

		return version_compare( DM_VERSION, $data->version, '<' );
	}

	/**
	 * Method for calling Dark Matter Cloud to get the plugin information.
	 *
	 * @since 2.2.0
	 *
	 * @return false|mixed Dark Matter plugin information.
	 */
	private function request() {
		$response = get_transient( $this->cache_key );

		if ( ! empty( $response ) ) {
			return json_decode( wp_remote_retrieve_body( $response ) );
		}

		/**
		 * Construct the URL for Dark Matter Cloud.
		 *
		 * @link https://github.com/YahnisElsts/wp-update-server
		 */
		$url = add_query_arg(
			[
				'action' => 'get_metadata',
				'slug'   => 'dark-matter',
			],
			'https://plugins.darkmattercloud.com/'
		);

		$response = wp_remote_get(
			$url,
			[
				'timeout' => 3,
				'headers' => [
					'Accept' => 'application/json',
				],
			]
		);

		/**
		 * Validate the response and ensure it is useful.
		 */
		if (
			is_wp_error( $response )
			|| 200 === wp_remote_retrieve_response_code( $response )
		) {
			$body = wp_remote_retrieve_body( $response );

			/**
			 * Update the transient and return the encoded JSON.
			 */
			if ( ! empty( $body ) ) {
				set_transient( $this->cache_key, $response, DAY_IN_SECONDS );

				return json_decode( wp_remote_retrieve_body( $response ) );
			}
		}

		/**
		 * Got here, then the validation failed.
		 */
		return false;
	}

	/**
	 * Gets the plugin information.
	 *
	 * @since 2.2.0
	 *
	 * @param false|object|array $result The result object or array. Default false.
	 * @param string             $action The type of information being requested from the Plugin Installation API.
	 * @param object             $args Plugin API arguments.
	 * @return false|object|array
	 */
	public function plugin_info( $result = false, $action = '', $args = null ) {
		/**
		 * Ensure we are only requesting the API at the most appropriate points.
		 */
		if ( 'plugin_information' !== $action || 'dark-matter' !== $args->slug ) {
			return false;
		}

		/**
		 * Retrieve the plugin data.
		 */
		$data = $this->request();

		if ( empty( $data ) ) {
			return false;
		}

		$result = new stdClass();

		$result->name           = $data->name;
		$result->slug           = $data->slug;
		$result->version        = $data->version;
		$result->new_version    = $data->version;
		$result->tested         = $data->tested;
		$result->requires       = $data->requires;
		$result->author         = $data->author;
		$result->author_profile = $data->author_homepage;
		$result->download_link  = $data->download_url;
		$result->trunk          = $data->download_url;
		$result->requires_php   = $data->requires_php;
		$result->last_updated   = $data->last_updated;

		$result->sections = [
			'description'  => $data->sections->description,
			'installation' => $data->sections->installation,
			'changelog'    => $data->sections->changelog,
		];

		if ( ! empty( $data->banners ) ) {
			$result->banners = [
				'low'  => $data->banners->low,
				'high' => $data->banners->high,
			];
		}

		return $result;
	}

	/**
	 * Adds any updates to Dark Matter to the relevant transient in WordPress.
	 *
	 * @since 2.2.0
	 *
	 * @param mixed $value Value of site transient.
	 * @return mixed New value of site transient.
	 */
	public function push_update( $value = null ) {
		$data              = $this->request();
		$data->new_version = $data->version;
		$data->package     = $data->download_url;

		if ( $this->needs_update( $data ) ) {
			$value->response[ $this->plugin_slug ] = $data;
		} else {
			$value->no_update[ $this->plugin_slug ] = $data;
		}

		return $value;
	}
}

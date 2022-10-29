<?php
/**
 * Class DM_REST_Restricted_Controller
 *
 * @package DarkMatter
 * @since 2.0.0
 */

/**
 * Class DM_REST_Restricted_Controller
 *
 * @since 2.0.0
 */
class DM_REST_Restricted_Controller extends WP_REST_Controller {
	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->namespace = 'dm/v1';
		$this->rest_base = 'restricted';
	}

	/**
	 * Add a domain to the Restricted domains list.
	 *
	 * @since 2.0.0
	 *
	 * @param  WP_REST_Request $request Current request.
	 * @return WP_REST_Response|mixed WP_REST_Response on success. WP_Error on failure.
	 */
	public function create_item( $request ) {
		$db = DarkMatter_Restrict::instance();

		$domain = ( isset( $request['domain'] ) ? $request['domain'] : '' );

		$result = $db->add( $domain );

		/**
		 * Return errors as-is. This is maintain consistency and parity with the
		 * WP CLI commands.
		 */
		if ( is_wp_error( $result ) ) {
			return rest_ensure_response( $result );
		}

		$response = rest_ensure_response(
			array(
				'domain' => $domain,
			)
		);

		$response->set_status( '201' );

		return $response;
	}

	/**
	 * Checks if a given request has access to add a Restricted domain.
	 *
	 * @since 2.0.0
	 *
	 * @param  WP_REST_Request $request Current request.
	 * @return boolean True if the current user is a Super Admin. False otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		/**
		 * Allows the override of the default permission for the restricted domain management.
		 *
		 * @since 2.1.2
		 *
		 * @param string $capability Capability required to manage domains (upgrade_network / Super Admin).
		 * @param string $context The context the permission is checked.
		 */
		return current_user_can( apply_filters( 'dark_matter_restricted_permission', 'upgrade_network', 'rest-create' ) );
	}

	/**
	 * Delete a domain to the Restricted domains list.
	 *
	 * @since 2.0.0
	 *
	 * @param  WP_REST_Request $request Current request.
	 * @return WP_REST_Response|mixed WP_REST_Response on success. WP_Error on failure.
	 */
	public function delete_item( $request ) {
		$db = DarkMatter_Restrict::instance();

		$domain = ( isset( $request['domain'] ) ? $request['domain'] : '' );

		$result = $db->delete( $domain );

		/**
		 * Return errors as-is. This is maintain consistency and parity with the
		 * WP CLI commands.
		 */
		if ( is_wp_error( $result ) ) {
			return rest_ensure_response( $result );
		}

		return rest_ensure_response(
			array(
				'deleted' => true,
				'domain'  => $domain,
			)
		);
	}

	/**
	 * Checks if a given request has access to delete Restricted domains.
	 *
	 * @since 2.0.0
	 *
	 * @param  WP_REST_Request $request Current request.
	 * @return boolean True if the current user is a Super Admin. False otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		/** This action is documented in domain-mapping/rest/class-dm-rest-restricted-controller.php */
		return current_user_can( apply_filters( 'dark_matter_restricted_permission', 'upgrade_network', 'rest-delete' ) );
	}

	/**
	 * Return the Restricted domains as a list in REST response.
	 *
	 * @since 2.0.0
	 *
	 * @param  WP_REST_Request $request Current request.
	 * @return WP_REST_Response|mixed WP_REST_Response on success. WP_Error on failure.
	 */
	public function get_items( $request ) {
		$db = DarkMatter_Restrict::instance();

		return rest_ensure_response( $db->get() );
	}

	/**
	 * Checks if a given request has access to retrieve a list Restricted
	 * domains.
	 *
	 * @since 2.0.0
	 *
	 * @param  WP_REST_Request $request Current request.
	 * @return boolean True if the current user is a Super Admin. False otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		/** This action is documented in domain-mapping/rest/class-dm-rest-restricted-controller.php */
		return current_user_can( apply_filters( 'dark_matter_restricted_permission', 'upgrade_network', 'rest-get' ) );
	}

	/**
	 * Register REST API routes for Restricted domains.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
			]
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base,
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
			]
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base,
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			]
		);
	}
}

/**
 * Setup the REST Controller for Domains for use.
 *
 * @since 2.0.0
 *
 * @return void
 */
function dark_matter_restricted_rest() {
	$controller = new DM_REST_Restricted_Controller();
	$controller->register_routes();
}
add_action( 'rest_api_init', 'dark_matter_restricted_rest' );

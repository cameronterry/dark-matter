<?php
/**
 * REST API for management of Restricted domains.
 *
 * @since 2.0.0
 *
 * @package DarkMatterPlugin\DomainMapping
 */

namespace DarkMatter\DomainMapping\REST;

use DarkMatter\DomainMapping\Data\RestrictedDomain;
use DarkMatter\DomainMapping\Data\RestrictedDomainQuery;
use \DarkMatter\DomainMapping\Manager;

/**
 * Class Restricted
 *
 * This was previously called `DM_REST_Restricted_Controller`.
 *
 * @since 2.0.0
 */
class Restricted extends \WP_REST_Controller {
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
	 * @param  \WP_REST_Request $request Current request.
	 * @return \WP_REST_Response WP_REST_Response on success. WP_Error on failure.
	 */
	public function create_item( $request ) {
		$domain = ( isset( $request['domain'] ) ? $request['domain'] : '' );

		$data = new RestrictedDomain();
		$result = $data->add(
			[
				'domain' => $domain,
			]
		);

		/**
		 * Return errors as-is. This is to maintain consistency and parity with the
		 * WP CLI commands.
		 */
		if ( is_wp_error( $result ) ) {
			return rest_ensure_response( $result );
		} elseif ( false === $result ) {
			return rest_ensure_response(
				new \WP_Error(
					'restricted_unknown_error',
					__( 'Unknown error occurred.', 'dark-matter' )
				)
			);
		}

		$response = rest_ensure_response(
			[
				'domain' => $domain,
			]
		);

		$response->set_status( '201' );

		return $response;
	}

	/**
	 * Checks if a given request has access to add a Restricted domain.
	 *
	 * @since 2.0.0
	 *
	 * @param  \WP_REST_Request $request Current request.
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
	 * @param  \WP_REST_Request $request Current request.
	 * @return \WP_REST_Response|mixed WP_REST_Response on success. WP_Error on failure.
	 */
	public function delete_item( $request ) {
		$domain = ( isset( $request['domain'] ) ? $request['domain'] : '' );

		$data   = new RestrictedDomain();
		$result = $data->delete( $request['domain'] );

		/**
		 * Return errors as-is. This is maintain consistency and parity with the
		 * WP CLI commands.
		 */
		if ( is_wp_error( $result ) ) {
			return rest_ensure_response( $result );
		} elseif ( false === $result ) {
			return rest_ensure_response(
				new \WP_Error(
					'restricted_unknown_error',
					__( 'Unknown error occurred.', 'dark-matter' )
				)
			);
		}

		return rest_ensure_response(
			[
				'deleted' => true,
				'domain'  => $domain,
			]
		);
	}

	/**
	 * Checks if a given request has access to delete Restricted domains.
	 *
	 * @since 2.0.0
	 *
	 * @param  \WP_REST_Request $request Current request.
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
	 * @param  \WP_REST_Request $request Current request.
	 * @return \WP_REST_Response WP_REST_Response on success. WP_Error on failure.
	 */
	public function get_items( $request ) {
		$query = new RestrictedDomainQuery(
			[
				'number' => 100,
			]
		);

		return rest_ensure_response( $query->records );
	}

	/**
	 * Checks if a given request has access to retrieve a list Restricted
	 * domains.
	 *
	 * @since 2.0.0
	 *
	 * @param  \WP_REST_Request $request Current request.
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
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_item' ],
				'permission_callback' => [ $this, 'create_item_permissions_check' ],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base,
			[
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_item' ],
				'permission_callback' => [ $this, 'delete_item_permissions_check' ],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base,
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
			]
		);
	}
}

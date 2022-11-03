<?php
/**
 * REST API for management domains.
 *
 * @package DarkMatter
 * @since 2.0.0
 */

namespace DarkMatter\DomainMapping\REST;

use DarkMatter\DomainMapping\Data;
use \DarkMatter\DomainMapping\Manager;

/**
 * Class Domains
 *
 * This was previous called `DM_REST_Domains_Controller`.
 *
 * @since 2.0.0
 */
class Domains extends \WP_REST_Controller {
	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->namespace        = 'dm/v1';
		$this->rest_base        = 'domain';
		$this->rest_base_plural = 'domains';
	}

	/**
	 * Add a domain to the Site.
	 *
	 * @since 2.0.0
	 *
	 * @param  \WP_REST_Request $request Current request.
	 * @return \WP_REST_Response|mixed WP_REST_Response on success. WP_Error on failure.
	 */
	public function create_item( $request ) {
		$db = Manager\Domain::instance();

		$item = $this->prepare_item_for_database( $request );

		$result = $db->add( $item['domain'], $item['is_primary'], $item['is_https'], $request['force'], $item['is_active'] );

		/**
		 * Return errors as-is. This is maintain consistency and parity with the
		 * WP CLI commands.
		 */
		if ( is_wp_error( $result ) ) {
			return rest_ensure_response( $result );
		}

		/**
		 * Prepare response for successfully adding a domain.
		 */
		$response = rest_ensure_response( $result );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $result->domain ) ) );

		return $response;
	}

	/**
	 * Checks if a given request has access to add a domain.
	 *
	 * @since 2.0.0
	 *
	 * @param  \WP_REST_Request $request Current request.
	 * @return boolean True if the current user is a Super Admin. False otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		/** This action is documented in domain-mapping/classes/class-dm-ui.php */
		return current_user_can( apply_filters( 'dark_matter_domain_permission', 'upgrade_network', 'rest-create' ) );
	}

	/**
	 * Delete a domain.
	 *
	 * @since 2.0.0
	 *
	 * @param  \WP_REST_Request $request Current request.
	 * @return \WP_REST_Response|mixed WP_REST_Response on success. WP_Error on failure.
	 */
	public function delete_item( $request ) {
		$db = Manager\Domain::instance();

		$result = $db->delete( $request['domain'], $request['force'] );

		/**
		 * Return errors as-is. This is maintain consistency and parity with the
		 * WP CLI commands.
		 */
		if ( is_wp_error( $result ) ) {
			return rest_ensure_response( $result );
		}

		/**
		 * Handle the response for the REST endpoint.
		 */
		$response = rest_ensure_response(
			array(
				'deleted' => true,
				'domain'  => $request['domain'],
			)
		);

		return $response;
	}

	/**
	 * Checks if a given request has access to delete a domain.
	 *
	 * @since 2.0.0
	 *
	 * @param  \WP_REST_Request $request Current request.
	 * @return boolean True if the current user is a Super Admin. False otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		/** This action is documented in domain-mapping/classes/class-dm-ui.php */
		return current_user_can( apply_filters( 'dark_matter_domain_permission', 'upgrade_network', 'rest-delete' ) );
	}

	/**
	 * Return the Restricted domains as a list in REST response.
	 *
	 * @since 2.0.0
	 *
	 * @param  \WP_REST_Request $request Current request.
	 * @return \WP_REST_Response|mixed WP_REST_Response on success. WP_Error on failure.
	 */
	public function get_item( $request ) {
		$db = Manager\Domain::instance();

		$result = $db->get( $request['domain'] );

		/**
		 * Return errors as-is. This is maintain consistency and parity with the
		 * WP CLI commands.
		 */
		if ( is_wp_error( $result ) ) {
			return rest_ensure_response( $result );
		}

		/**
		 * Handle the response for the REST endpoint.
		 */
		$response = $this->prepare_item_for_response( $result, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * JSON Schema definition for Domain.
	 *
	 * @since 2.0.0
	 *
	 * @return array JSON Schema definition.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'Domain',
			'type'       => 'object',
			'properties' => array(
				'id'         => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Unique identifier for the object.', 'dark-matter' ),
					'readonly'    => true,
					'type'        => 'integer',
				),
				'domain'     => array(
					'context'     => array( 'view', 'edit' ),
					'default'     => '',
					'description' => __( 'Domain name.', 'dark-matter' ),
					'required'    => true,
					'type'        => 'string',
				),
				'is_primary' => array(
					'context'     => array( 'view', 'edit' ),
					'default'     => null,
					'description' => __( 'Domain is the primary domain for the Site.', 'dark-matter' ),
					'required'    => false,
					'type'        => 'boolean',
				),
				'is_active'  => array(
					'context'     => array( 'view', 'edit' ),
					'default'     => null,
					'description' => __( 'Domain is currently being used.', 'dark-matter' ),
					'required'    => false,
					'type'        => 'boolean',
				),
				'is_https'   => array(
					'context'     => array( 'view', 'edit' ),
					'default'     => null,
					'description' => __( 'Domain is to be available on the HTTPS protocol.', 'dark-matter' ),
					'required'    => false,
					'type'        => 'boolean',
				),
				'type'       => array(
					'context'     => array( 'view', 'edit' ),
					'default'     => null,
					'description' => __( 'Type of domain.', 'dark-matter' ),
					'required'    => false,
					'type'        => 'integer',
				),
				'site'       => array(
					'description' => __( 'Site ID the domain is assigned against.', 'dark-matter' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
					'properties'  => array(
						'blog_id'      => array(
							'context'     => array( 'view', 'edit' ),
							'description' => __( 'Site ID.', 'dark-matter' ),
							'readonly'    => true,
							'required'    => false,
							'type'        => 'integer',
						),
						'site_id'      => array(
							'context'     => array( 'view', 'edit' ),
							'description' => __( 'The ID of the site\'s parent network.', 'dark-matter' ),
							'readonly'    => true,
							'required'    => false,
							'type'        => 'integer',
						),
						'domain'       => array(
							'context'     => array( 'view', 'edit' ),
							'description' => __( 'Domain of the site.', 'dark-matter' ),
							'readonly'    => true,
							'required'    => false,
							'type'        => 'string',
						),
						'path'         => array(
							'context'     => array( 'view', 'edit' ),
							'description' => __( 'Path of the site.', 'dark-matter' ),
							'readonly'    => true,
							'required'    => false,
							'type'        => 'string',
						),
						'registered'   => array(
							'context'     => array( 'view', 'edit' ),
							'description' => __( 'The date on which the site was created or registered.', 'dark-matter' ),
							'format'      => 'date-time',
							'readonly'    => true,
							'required'    => false,
							'type'        => 'string',
						),
						'last_updated' => array(
							'context'     => array( 'view', 'edit' ),
							'description' => __( 'The date and time on which site settings were last updated.', 'dark-matter' ),
							'format'      => 'date-time',
							'readonly'    => true,
							'required'    => false,
							'type'        => 'string',
						),
						'public'       => array(
							'context'     => array( 'view', 'edit' ),
							'description' => __( 'Whether the site should be treated as public.', 'dark-matter' ),
							'readonly'    => true,
							'required'    => false,
							'type'        => 'integer',
						),
						'archived'     => array(
							'context'     => array( 'view', 'edit' ),
							'description' => __( 'Whether the site should be treated as archived.', 'dark-matter' ),
							'readonly'    => true,
							'required'    => false,
							'type'        => 'boolean',
						),
						'mature'       => array(
							'context'     => array( 'view', 'edit' ),
							'description' => __( 'Whether the site should be treated as mature.', 'dark-matter' ),
							'readonly'    => true,
							'required'    => false,
							'type'        => 'boolean',
						),
						'spam'         => array(
							'context'     => array( 'view', 'edit' ),
							'description' => __( 'Whether the site should be treated as spam.', 'dark-matter' ),
							'readonly'    => true,
							'required'    => false,
							'type'        => 'boolean',
						),
						'deleted'      => array(
							'context'     => array( 'view', 'edit' ),
							'description' => __( 'Whether the site should be treated as deleted.', 'dark-matter' ),
							'readonly'    => true,
							'required'    => false,
							'type'        => 'boolean',
						),
					),
				),
			),
		);

		return $schema;
	}

	/**
	 * Return a list of Domains.
	 *
	 * @since 2.0.0
	 *
	 * @param  \WP_REST_Request $request Current request.
	 * @return \WP_REST_Response|mixed WP_REST_Response on success. WP_Error on failure.
	 */
	public function get_items( $request ) {
		$site_id = null;

		/**
		 * Handle the processing of the Site ID parameter if it is provided. If
		 * not, then set the $site_id to the Current Blog ID unless it is the
		 * main site calling this endpoint. For the main site, we return all the
		 * Domains for all Sites on the WordPress Network.
		 */
		if ( isset( $request['site_id'] ) ) {
			$site_id = $request['site_id'];
		} elseif ( ! is_main_site() ) {
			$site_id = get_current_blog_id();
		}

		$db = Manager\Domain::instance();

		$response = array();

		$result = $db->get_domains( $site_id );

		/**
		 * Return errors as-is. This is maintain consistency and parity with the
		 * WP CLI commands.
		 */
		if ( is_wp_error( $result ) ) {
			return rest_ensure_response( $result );
		}

		/**
		 * Process the domains and prepare each for the JSON response.
		 */
		foreach ( $result as $dm_domain ) {
			$response[] = $this->prepare_item_for_response( $dm_domain, $request );
		}

		return rest_ensure_response( $response );
	}

	/**
	 * Checks if a given request has access to get a domain or list of domains.
	 *
	 * @since 2.0.0
	 *
	 * @param  \WP_REST_Request $request Current request.
	 * @return boolean True if the current user is a Super Admin. False otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		/** This action is documented in domain-mapping/classes/class-dm-ui.php */
		return current_user_can( apply_filters( 'dark_matter_domain_permission', 'upgrade_network', 'rest-get' ) );
	}

	/**
	 * Prepare item for adding to the database.
	 *
	 * @since 2.0.0
	 *
	 * @param  \WP_REST_Request $request Current request.
	 * @return array Data provided by the call to the endpoint.
	 */
	protected function prepare_item_for_database( $request ) {
		$item = array(
			'domain'     => '',
			'is_primary' => null,
			'is_https'   => null,
			'is_active'  => null,
			'type'       => null,
		);

		$method = $request->get_method();

		foreach ( $item as $key => $default ) {
			$value = $default;

			if ( isset( $request[ $key ] ) ) {
				$value = $request[ $key ];
			}

			if ( \WP_REST_Server::CREATABLE === $method && null === $value && 'is_primary' === $key ) {
				$value = false;
			}

			if ( \WP_REST_Server::CREATABLE === $method && null === $value && 'is_https' === $key ) {
				$value = false;
			}

			if ( \WP_REST_Server::CREATABLE === $method && null === $value && 'is_active' === $key ) {
				$value = true;
			}

			$item[ $key ] = $value;
		}

		return $item;
	}

	/**
	 * Prepares a single domain output for response.
	 *
	 * @since 2.0.0
	 *
	 * @param  Data\Domain      $item    Domain object to be prepared for response.
	 * @param  \WP_REST_Request $request Current request.
	 * @return array Prepared item for REST response.
	 */
	public function prepare_item_for_response( $item, $request ) {
		$fields = $this->get_fields_for_response( $request );

		$data = array();

		if ( in_array( 'id', $fields, true ) ) {
			$data['id'] = $item->id;
		}

		if ( in_array( 'domain', $fields, true ) ) {
			$data['domain'] = $item->domain;
		}

		if ( in_array( 'is_primary', $fields, true ) ) {
			$data['is_primary'] = $item->is_primary;
		}

		if ( in_array( 'is_active', $fields, true ) ) {
			$data['is_active'] = $item->active;
		}

		if ( in_array( 'is_https', $fields, true ) ) {
			$data['is_https'] = $item->is_https;
		}

		if ( in_array( 'type', $fields, true ) ) {
			$data['type'] = $item->type;
		}

		if ( in_array( 'site', $fields, true ) ) {
			$site_data = get_site( $item->blog_id );

			if ( ! empty( $site_data ) ) {
				$data['site'] = $site_data->to_array();

				$data['site']['blog_id']  = absint( $data['site']['blog_id'] );
				$data['site']['site_id']  = absint( $data['site']['site_id'] );
				$data['site']['public']   = absint( $data['site']['public'] );
				$data['site']['archived'] = boolval( $data['site']['archived'] );
				$data['site']['mature']   = boolval( $data['site']['mature'] );
				$data['site']['spam']     = boolval( $data['site']['spam'] );
				$data['site']['deleted']  = boolval( $data['site']['deleted'] );

				if ( '0000-00-00 00:00:00' === $data['site']['registered'] ) {
					$data['site']['registered'] = null;
				} else {
					$data['site']['registered'] = mysql_to_rfc3339( $data['site']['registered'] );
				}

				if ( '0000-00-00 00:00:00' === $data['site']['last_updated'] ) {
					$data['site']['last_updated'] = null;
				} else {
					$data['site']['last_updated'] = mysql_to_rfc3339( $data['site']['last_updated'] );
				}
			} else {
				$data['site'] = null;
			}
		}

		return $data;
	}

	/**
	 * Register the routes for the REST API.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/(?P<domain>.+)',
			array(
				'args' => array(
					'domain' => array(
						'description' => __( 'Site ID to retrieve a list of Domains.', 'dark-matter' ),
						'required'    => true,
						'type'        => 'string',
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'schema'              => array( $this, 'get_item_schema' ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => array(
						'force' => array(
							'default'     => false,
							'description' => __( 'Force Dark Matter to remove the domain. This is required if you wish to remove a Primary domain from a Site.', 'dark-matter' ),
							'type'        => 'boolean',
						),
					),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base_plural,
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'schema'              => array( $this, 'get_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base_plural . '/(?P<site_id>[\d]+)',
			array(
				'args' => array(
					'site_id' => array(
						'description' => __( 'Site ID to retrieve a list of Domains.', 'dark-matter' ),
						'required'    => true,
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'schema'              => array( $this, 'get_item_schema' ),
				),
			)
		);
	}

	/**
	 * Update a domain for a Site.
	 *
	 * @since 2.0.0
	 *
	 * @param  \WP_REST_Request $request Current request.
	 * @return \WP_REST_Response|mixed WP_REST_Response on success. WP_Error on failure.
	 */
	public function update_item( $request ) {
		$db = Manager\Domain::instance();

		$item = $this->prepare_item_for_database( $request );

		$result = $db->update(
			$item['domain'],
			$item['is_primary'],
			$item['is_https'],
			$request['force'],
			$item['is_active'],
			$item['type']
		);

		/**
		 * Return errors as-is. This is maintain consistency and parity with the
		 * WP CLI commands.
		 */
		if ( is_wp_error( $result ) ) {
			return rest_ensure_response( $result );
		}

		/**
		 * Prepare response for successfully adding a domain.
		 */
		$response = $this->prepare_item_for_response( $result, $request );
		$response = rest_ensure_response( $response );

		return $response;
	}

	/**
	 * Checks if a given request has access to update a domain.
	 *
	 * @since 2.0.0
	 *
	 * @param  \WP_REST_Request $request Current request.
	 * @return boolean True if the current user is a Super Admin. False otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		/** This action is documented in domain-mapping/classes/class-dm-ui.php */
		return current_user_can( apply_filters( 'dark_matter_domain_permission', 'upgrade_network', 'rest-update' ) );
	}
}

<?php

class DM_REST_Domains_Controller extends WP_REST_Controller {
    public function __construct() {
        $this->namespace = 'dm/v1';
        $this->rest_base = 'domains';
    }

    public function create_item( $request ) {

    }

    public function create_item_permissions_check( $request ) {
        return current_user_can( 'upgrade_network' );
    }

    public function delete_item( $request ) {

    }

    public function delete_item_permissions_check( $request ) {
        return current_user_can( 'upgrade_network' );
    }

    public function get_item( $request ) {

    }

    /**
     * JSON Schema definition for Domain.
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
                    'description' => __( 'Unique identifier for the object.', 'dark-matter' ),
                    'type'        => 'integer',
                    'context'     => array( 'view', 'edit' ),
                    'readonly'    => true,
                ),
                'domain'     => array(
                    'description' => __( 'Domain name.', 'dark-matter' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                ),
                'is_primary' => array(
                    'description' => __( 'Domain is the primary domain for the Site.', 'dark-matter' ),
                    'type'        => 'boolean',
                    'context'     => array( 'view', 'edit' ),
                ),
                'is_active'  => array(
                    'description' => __( 'Domain is currently being used.', 'dark-matter' ),
                    'type'        => 'boolean',
                    'context'     => array( 'view', 'edit' ),
                ),
                'is_https'   => array(
                    'description' => __( 'Domain is to be available on the HTTPS protocol.', 'dark-matter' ),
                    'type'        => 'boolean',
                    'context'     => array( 'view', 'edit' ),
                ),
                'blog_id'    => array(
                    'description' => __( 'Site ID the domain is assigned against.', 'dark-matter' ),
                    'type'        => 'integer',
                    'context'     => array( 'view', 'edit' ),
                    'readonly'    => true,
                ),
            ),
        );

        return $schema;
    }

    /**
     * Return a list of Domains.
     *
     * @param  WP_REST_Request        $request Current request.
     * @return WP_REST_Response|mixed          WP_REST_Response on success. WP_Error on failure.
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
        } else if ( ! is_main_site() ) {
            $site_id = get_current_blog_id();
        }

        $db = DarkMatter_Domains::instance();

        $response = array();

        $result = $db->get_domains( $site_id );

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

    public function get_items_permissions_check( $request ) {
        return current_user_can( 'upgrade_network' );
    }

    protected function prepare_item_for_database( $request ) {

    }

    /**
     * Prepares a single domain output for response.
     *
     * @param  DM_Domain       $item    Domain object to be prepared for response.
     * @param  WP_REST_Request $request Current request.
     * @return array                    Prepared item for REST response.
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

        if ( in_array( 'blog_id', $fields, true ) ) {
            $data['blog_id'] = $item->blog_id;
        }

        return $data;
    }

    public function register_routes() {
        register_rest_route( $this->namespace, $this->rest_base, array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_items' ),
            'permission_callback' => array( $this, 'get_items_permissions_check' ),
            'schema'              => array( $this, 'get_item_schema' ),
        ) );

        register_rest_route( $this->namespace, $this->rest_base . '/(?P<site_id>[\d]+)', array(
            'args' => array(
                'site_id' => array(
                    'description' => __( 'Site ID to retrieve a list of Domains.', 'dark-matter' ),
                    'type'        => 'integer',
                ),
            ),
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'schema'              => array( $this, 'get_item_schema' ),
            )
        ) );
    }

    public function update_item( $request ) {

    }

    public function update_item_permissions_check( $request ) {
        return current_user_can( 'upgrade_network' );
    }
}

/**
 * Setup the REST Controller for Domains for use.
 *
 * @return void
 */
function dark_matter_domains_rest() {
    $controller = new DM_REST_Domains_Controller();
    $controller->register_routes();
}
add_action( 'rest_api_init', 'dark_matter_domains_rest' );
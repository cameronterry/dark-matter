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

    public function get_items( $request ) {

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
            $data['is_active'] = $item->is_active;
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
<?php

class DM_REST_Restricted_Controller extends WP_REST_Controller {
    public function __construct() {
        $this->namespace = 'dm/v1';
        $this->rest_base = 'restricted';
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
     * Return the Restricted domains as a list in REST response.
     *
     * @param  WP_REST_Request        $request Current request.
     * @return WP_REST_Response|mixed          WP_REST_Response on success. WP_Error on failure.
     */
    public function get_items( $request ) {
        $db = DarkMatter_Restrict::instance();

        return rest_ensure_response( $db->get() );
    }

    public function get_items_permissions_check( $request ) {
        return current_user_can( 'upgrade_network' );
    }

    protected function prepare_item_for_database( $request ) {

    }

    /**
     * Register REST API routes for Restricted domains.
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route( $this->namespace, $this->rest_base, [
            'methods'  => WP_REST_Server::READABLE,
            'callback' => array( $this, 'get_items' ),
            'permission_callback' => array( $this, 'get_items_permissions_check' ),
        ] );
    }
}

/**
 * Setup the REST Controller for Domains for use.
 *
 * @return void
 */
function dark_matter_restricted_rest() {
    $controller = new DM_REST_Restricted_Controller();
    $controller->register_routes();
}
add_action( 'rest_api_init', 'dark_matter_restricted_rest' );
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

    public function get_items( $request ) {

    }

    public function get_items_permissions_check( $request ) {
        return current_user_can( 'upgrade_network' );
    }

    protected function prepare_item_for_database( $request ) {

    }

    public function prepare_item_for_response( $item, $request ) {

    }

    public function register_routes() {

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
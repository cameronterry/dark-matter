<?php

class DM_REST_Restricted_Controller extends WP_REST_Posts_Controller {
    public function __construct() {
        $this->namespace = 'darkmatter/restricted';
    }

    public function create_item( $request ) {

    }

    public function create_item_permissions_check( $request ) {

    }

    public function delete_item( $request ) {

    }

    public function delete_item_permissions_check( $request ) {

    }

    public function get_item( $request ) {

    }

    public function get_items( $request ) {

    }

    public function get_items_permissions_check( $request ) {

    }

    protected function prepare_item_for_database( $request ) {

    }

    public function prepare_item_for_response( $item, $request ) {

    }

    public function register_routes() {

    }
}
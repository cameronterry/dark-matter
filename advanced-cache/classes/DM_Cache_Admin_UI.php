<?php
defined( 'ABSPATH' ) || die();

class DM_Cache_Admin_UI {
    /**
     * DM_Cache_Admin_UI constructor.
     */
    public function __construct() {
        /**
         * Only show the Admin Bar elements if on the mapped / front-end domain.
         */
        if ( is_admin() || ! is_user_logged_in() ) {
            return;
        }

        /**
         * Ensure the current user is an admin.
         */
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
    }

    /**
     * Return the Singleton Instance of the class.
     *
     * @return bool|DM_Advanced_Cache
     */
    public static function instance() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }
}
DM_Cache_Admin_UI::instance();
<?php
defined( 'ABSPATH' ) || die();

class DM_Cache_Admin_UI {
    /**
     * DM_Cache_Admin_UI constructor.
     */
    public function __construct() {
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
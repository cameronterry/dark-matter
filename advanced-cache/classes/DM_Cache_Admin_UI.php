<?php
defined( 'ABSPATH' ) || die();

class DM_Cache_Admin_UI {
    /**
     * DM_Cache_Admin_UI constructor.
     */
    public function __construct() {
        add_action( 'init', [ $this, 'init' ] );
    }

    public function init() {
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

        add_action( 'admin_bar_menu', [ $this, 'admin_bar_info' ], 100 );
    }

    /**
     * Add the information box to the Admin Bar.
     *
     * @param WP_Admin_Bar $admin_bar
     */
    public function admin_bar_info( $admin_bar = null ) {
        $cache_info = $this->get_cache_info();

        /**
         * Retrieve the standard variant. All except the more exotic websites will use this version.
         */
        $standard_variant = $cache_info->get_standard();

        if ( empty( $standard_variant ) ) {
            $icon = '<span class="ab-icon dashicons dashicons-warning"></span>';
            $text = 'Not cached.';
        }
        else {
            $dashicon = ( 'Expired' === $standard_variant['remaining'] ? 'dismiss' : 'yes-alt' );

            $icon = sprintf( '<span class="ab-icon dashicons dashicons-%1$s"></span>', esc_attr( $dashicon ) );
            $text = $standard_variant['remaining'];
        }

        $admin_bar->add_menu( [
            'id'     => 'darkmatter-fullpage',
            'parent' => null,
            'group'  => null,
            'title'  => $icon . $text,
            'meta'   => [
                'title' => __( 'Cache details', 'dark-matter' ),
            ],
        ] );
    }

    /**
     * Retrieves the cache information.
     *
     * @return DM_Cache_Info Object containing the relevant cache information.
     */
    private function get_cache_info() {
        return new DM_Cache_Info( $this->get_url() );
    }

    /**
     * Returns the current URL.
     *
     * @return string Current URL.
     */
    private function get_url() {
        $protocol = 'http://';
        if ( isset( $_SERVER['HTTPS'] ) ) {
            if ( 'on' == strtolower( $_SERVER['HTTPS'] ) || '1' == $_SERVER['HTTPS'] ) {
                $protocol = 'https://';
            }
        } elseif ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
            $protocol = 'https://';
        }

        $host = rtrim( trim( $_SERVER['HTTP_HOST'] ), '/' );
        $path = ltrim( trim( $_SERVER['REQUEST_URI'] ), '/' );

        return $protocol . $host . '/' . $path;
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
<?php

defined( 'ABSPATH' ) || die;

class DM_UI {
    /**
     * Constructor
     */
    public function __construct() {
        /**
         * The root website cannot be mapped.
         */
        if ( is_main_site() ) {
            return;
        }

        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
    }

    /**
     * Initialise the admin menu and prep the hooks for the CSS and JavaScript
     * includes.
     *
     * @return void
     */
    public function admin_menu() {
        $hook_suffix = add_options_page( __( 'Domain Mappings', 'dark-matter' ), __( 'Domains', 'dark-matter' ), 'switch_themes', 'domains', array(
            $this, 'page'
        ) );

        add_action( 'load-' . $hook_suffix, array( $this, 'enqueue' ) );
    }

    /**
     * Enqueue assets for the Admin Page.
     *
     * @return void
     */
    public function enqueue() {
        wp_register_script( 'dark-matter-domains', DM_PLUGIN_URL . 'domain-mapping/build/build' . ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' ) . '.js', [], DM_VERSION, true );

        wp_localize_script( 'dark-matter-domains', 'dmSettings', array(
            'rest_root' => get_rest_url(),
            'nonce'     => wp_create_nonce( 'wp_rest' ),
        ) );

        wp_enqueue_script( 'dark-matter-domains' );

        wp_enqueue_style( 'dark-matter-domains', DM_PLUGIN_URL . 'domain-mapping/build/build' . ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' ) . '.css', [], DM_VERSION );
    }

    /**
     * Very basic HTML output for the
     *
     * @return void
     */
    public function page() {
    ?>
        <div id="root"></div>
    <?php
    }
}

new DM_UI();
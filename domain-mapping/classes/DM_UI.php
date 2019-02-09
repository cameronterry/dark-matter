<?php

defined( 'ABSPATH' ) || die;

class DM_UI {
    private $hook_suffix = '';

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
    }

    /**
     * Initialise the admin menu and prep the hooks for the CSS and JavaScript
     * includes.
     *
     * @return void
     */
    public function admin_menu() {
        $hook_suffix = add_options_page( __( 'Domain Mappings', 'dark-matter' ), __( 'Domains', 'dark-matter' ), 'switch_themes', 'domains', [
            $this, 'page'
        ] );

        add_action( 'load-' . $hook_suffix, [ $this, 'enqueue' ] );
    }

    /**
     * Enqueue assets for the Admin Page.
     *
     * @return void
     */
    public function enqueue() {
        wp_register_script( 'dark-matter-domains', plugins_url() . '/build/index.js', [], DM_VERSION, true );

        wp_enqueue_script( 'dark-matter-domains' );
    }

    /**
     * Very basic HTML output for the page.
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
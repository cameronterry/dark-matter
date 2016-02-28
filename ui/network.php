<?php

/** A bit of security for those who are too clever for their own good. */
defined( 'ABSPATH' ) or die();

function dark_matter_network_menu() {
  add_submenu_page( 'settings.php',
    __( 'Domain Mapping', 'darkmatter' ), __( 'Domain Mapping', 'darkmatter' ),
    'manage_network', 'dark_matter_admin_page',
    'dark_matter_network_settings_ui' );
}
add_action( 'network_admin_menu', 'dark_matter_network_menu' );

function dark_matter_network_settings_ui() { ?>
  <h1></h1>
<?php }

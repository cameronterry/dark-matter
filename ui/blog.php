<?php

/** A bit of security for those who are too clever for their own good. */
defined( 'ABSPATH' ) or die();

function dark_matter_blog_admin_menu() {
  $hook = add_options_page( __( 'Domain Mapping', 'darkmatter' ), __( 'Domain Mapping', 'darkmatter' ), 'manage_options', 'dark_matter_blog_settings', 'dark_matter_blog_domain_mapping' );
}
add_action( 'admin_menu', 'dark_matter_blog_admin_menu' );

function dark_matter_blog_domain_mapping() { ?>
  <div class="wrap">
    <h1><?php _e( 'Domain Mapping for this Blog', 'darkmatter' ); ?></h1>
    <table></table>
  </div>
<?php }

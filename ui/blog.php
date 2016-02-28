<?php

/** A bit of security for those who are too clever for their own good. */
defined( 'ABSPATH' ) or die();

function dark_matter_blog_admin_menu() {
  $hook = add_options_page( __( 'Domain Mapping', 'darkmatter' ), __( 'Domain Mapping', 'darkmatter' ), 'manage_options', 'dark_matter_blog_settings', 'dark_matter_blog_domain_mapping' );
}
add_action( 'admin_menu', 'dark_matter_blog_admin_menu' );

function dark_matter_blog_domain_mapping() { ?>
  <div class="wrap dark-matter-blog">
    <h1><?php _e( 'Domain Mapping for this Blog', 'darkmatter' ); ?></h1>
    <h2><?php _e( 'Mapped Domains', 'darkmatter' ); ?></h2>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Domain</th>
          <th>Is Main?</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th scope="row">1</th>
          <td>
            <a href="#">siteone.test</a>
          </td>
          <td>
            Yes
          </td>
          <td>
            Delete
          </td>
        </tr>
      </tbody>
    </table>
    <h2><?php _e( 'Add New Domain', 'darkmatter' ); ?></h2>
  </div>
<?php }

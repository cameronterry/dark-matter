<?php

/** A bit of security for those who are too clever for their own good. */
defined( 'ABSPATH' ) or die();

function dark_matter_blog_admin_menu() {
	if ( false === is_main_site() ) {
		$hook = add_options_page( __( 'Domain Mapping', 'darkmatter' ), __( 'Domain Mapping', 'darkmatter' ), 'manage_options', 'dark_matter_blog_settings', 'dark_matter_blog_domain_mapping' );
	}
}
add_action( 'admin_menu', 'dark_matter_blog_admin_menu' );

function dark_matter_blog_domain_mapping() {
  $domains = dark_matter_api_get_domains();
?>
  <div class="wrap dark-matter-blog">
    <h1><?php _e( 'Domain Mapping for this Blog', 'darkmatter' ); ?></h1>
    <h2><?php _e( 'Mapped Domains', 'darkmatter' ); ?></h2>
    <table id="dark-matter-blog-domains" data-delete-nonce="<?php echo( wp_create_nonce( 'delete_nonce' ) ); ?>" data-primary-nonce="<?php echo( wp_create_nonce( 'primary_nonce' ) ); ?>">
      <thead>
        <tr>
          <th>#</th>
          <th>Domain</th>
          <th>Features</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
          foreach ( $domains as $domain ) {
            dark_matter_blog_domain_mapping_row( $domain );
          }
        ?>
      </tbody>
    </table>
    <h2><?php _e( 'Add New Domain', 'darkmatter' ); ?></h2>
    <form id="dm_add_domain_form">
      <input id="dm_new_nonce" name="dm_new_nonce" type="hidden" value="<?php echo( wp_create_nonce( 'nonce' ) ); ?>" />
      <p>
        <label>
          Domain :
          <input id="dm_new_domain" name="dm_new_domain" type="text" value="" />
        </label>
      </p>
      <p>
        <label>
          Is Primary? :
          <input id="dm_new_is_primary" name="dm_new_is_primary" type="checkbox" value="yes" />
        </label>
      </p>
      <p>
        <label>
          Is HTTPS? :
          <input id="dm_new_is_https" name="dm_new_is_https" type="checkbox" value="yes" />
        </label>
      </p>
      <p>
        <button id="dm_new_add_domain" class="button button-primary">Add Domain</button>
      </p>
    </form>
  </div>
<?php }

function dark_matter_blog_domain_mapping_row( $data ) {
  $features = array();

  if ( $data->is_primary ) {
    $features[] = 'Primary';
  }

  if ( $data->is_https ) {
    $features[] = 'HTTPS';
  }

  if ( $data->active ) {
    $features[] = 'Active';
  }
?>
  <tr id="domain-<?php echo( $data->id ); ?>" data-id="<?php echo( $data->id ); ?>" data-primary="<?php echo( $data->is_primary ); ?>">
    <th scope="row">1</th>
    <td>
      <?php printf( '<a href="http://%1$s">%1$s</a>', $data->domain ); ?>
    </td>
    <td>
      <?php echo( implode( ', ', $features ) ); ?>
    </td>
    <td>
      <?php if ( empty( $data->is_primary ) ) : ?>
        <button class="primary-domain button" title="<?php echo( $data->domain ); ?>">Make Primary</button>
      <?php endif; ?>
      <button class="delete-domain button">Delete</button>
    </td>
  </tr>
<?php }

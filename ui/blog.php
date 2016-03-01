<?php

/** A bit of security for those who are too clever for their own good. */
defined( 'ABSPATH' ) or die();

function dark_matter_blog_admin_menu() {
  $hook = add_options_page( __( 'Domain Mapping', 'darkmatter' ), __( 'Domain Mapping', 'darkmatter' ), 'manage_options', 'dark_matter_blog_settings', 'dark_matter_blog_domain_mapping' );
}
add_action( 'admin_menu', 'dark_matter_blog_admin_menu' );

function dark_matter_blog_domain_mapping() {
  $domains = dark_matter_api_get_domains();
?>
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
        <?php foreach ( $domains as $domain ) : ?>
          <tr id="domain-<?php echo( $domain->id ); ?>" data-id="<?php echo( $domain->id ); ?>">
            <th scope="row">1</th>
            <td>
              <?php printf( '<a href="http://%1$s">%1$s</a>', $domain->domain ); ?>
            </td>
            <td>
              Yes
            </td>
            <td>
              <button class="delete-domain button">Delete</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <script type="text/html" id="tmpl-domain-row">
      <tr id="domain-{{{id}}}" data-id="{{{id}}}" style="display:none;">
        <th scope="row">{{{data.number}}}</th>
        <td>
          <a href="#">{{{data.domain}}}</a>
        </td>
        <td>
          {{{data.is_primary}}}
        </td>
        <td>
          <button class="delete-domain button">Delete</button>
        </td>
      </tr>
    </script>
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
          Is Main? :
          <input id="dm_new_is_main" name="dm_new_is_main" type="checkbox" value="yes" />
        </label>
      </p>
      <p>
        <button id="dm_new_add_domain" class="button button-primary">Add Domain</button>
      </p>
    </form>
  </div>
<?php }

<?php

/** A bit of security for those who are too clever for their own good. */
defined( 'ABSPATH' ) or die();

function dark_matter_network_menu() {
  add_submenu_page( 'settings.php',
    __( 'Domain Mapping Settings', 'darkmatter' ), __( 'Domain Mapping', 'darkmatter' ),
    'manage_network', 'dark_matter_admin_page',
    'dark_matter_network_settings_ui' );
}
add_action( 'network_admin_menu', 'dark_matter_network_menu' );

function dark_matter_network_settings_ui() { ?>
  <div class="wrap">
    <h1>Domain Mapping Settings</h1>
    <form method="POST" action="edit.php?action=dark_matter_network_settings_ui">
      <h2><?php _e( 'Settings', 'darkmatter' ); ?></h2>
      <!--<table class="form-table">
  			<tbody>
          <tr>
    				<th scope="row"><label for="site_name">Network Title</label></th>
    				<td>
    					<input name="site_name" type="text" id="site_name" class="regular-text" value="WordPress Network Sites">
    				</td>
    			</tr>

    			<tr>
    				<th scope="row"><label for="admin_email">Network Admin Email</label></th>
    				<td>
    					<input name="admin_email" type="email" id="admin_email" aria-describedby="admin-email-desc" class="regular-text" value="cammy.wan.kenobi@gmail.com">
    					<p class="description" id="admin-email-desc">
    						This email address will receive notifications. Registration and support emails will also come from this address.					</p>
    				</td>
    			</tr>
  		  </tbody>
      </table>-->
      <p class="submit">
  		  <button type="submit" class="button button-primary" autocomplete="off">
          <i class="icon-ok icon-white"></i>
          Save Changes
        </button>
  		</p>
      <?php wp_nonce_field( 'dark_matter_network_settings','dark_matter_network_settings_nonce' ); ?>
    </form>
  </div>
<?php }

function dark_matter_network_settings_ui_postback() {
  if ( false === empty( $_POST ) && check_admin_referer( 'dark_matter_network_settings', 'dark_matter_network_settings_nonce' ) ) {

  }
}
add_action( 'dark_matter_network_settings_ui', 'dark_matter_network_settings_ui_postback' );

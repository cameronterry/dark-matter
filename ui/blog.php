<?php

/** A bit of security for those who are too clever for their own good. */
defined( 'ABSPATH' ) or die();

function dark_matter_blog_admin_menu() {
	if ( false === is_main_site() ) {
		$hook = add_options_page( __( 'Domain Mapping', 'darkmatter' ), __( 'Domain Mapping', 'darkmatter' ), 'activate_plugins', 'dark_matter_blog_settings', 'dark_matter_blog_domain_mapping' );
	}
}
add_action( 'admin_menu', 'dark_matter_blog_admin_menu' );

function dark_matter_blog_domain_mapping() {
	$domains = dark_matter_api_get_domains();

	if ( false === current_user_can( 'activate_plugins' ) ) {
		wp_die( 'Insufficient permissions.' );
	}
	
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
		<form action="<?php echo( admin_url( 'admin.php?action=dm_add_domain' ) ); ?>" method="POST">
			<input id="dm_new_nonce" name="dm_new_nonce" type="hidden" value="<?php echo( wp_create_nonce( 'darkmatter-add-domain' ) ); ?>" />
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
	$base_actions_url = admin_url( 'admin.php?id=' . $data->id );

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
			<?php printf( '<a href="%2$s://%1$s">%1$s</a>', $data->domain, ( $data->is_https ? 'https' : 'http' ) ); ?>
		</td>
		<td>
			<?php echo( implode( ', ', $features ) ); ?>
		</td>
		<td>
			<?php if ( empty( $data->is_primary ) ) : ?>
				<a class="primary-domain button"  href="<?php echo( wp_nonce_url( add_query_arg( 'action', 'dm_new_primary_domain', $base_actions_url ), 'darkmatter-new-primary-domain', 'dm_new_primary_nonce' ) ); ?>" title="Make '<?php echo( $data->domain ); ?>' the primary domain for this blog.">Make Primary</a>
			<?php endif; ?>

			<a class="primary-domain button"  href="<?php echo( wp_nonce_url( add_query_arg( 'action', ( $data->is_https ? 'dm_unset_domain_https' : 'dm_set_domain_https' ), $base_actions_url ), 'darkmatter-set-https-domain', 'dm_set_https_nonce' ) ); ?>" title="Make '<?php echo( $data->domain ); ?>' the primary domain for this blog.">
				<?php echo( $data->is_https ? 'Remove HTTPS' : 'Add HTTPS' ); ?>
			</a>

			<a class="button" href="<?php echo( wp_nonce_url( add_query_arg( 'action', 'dm_del_domain', $base_actions_url ), 'darkmatter-delete-domain', 'dm_del_nonce' ) ); ?>">Delete</a>
		</td>
	</tr>
<?php }

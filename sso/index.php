<?php

/**
 * Links:
 * - http://meta.stackexchange.com/questions/64260/how-does-sos-new-auto-login-feature-work/64274#64274
 * - http://stackoverflow.com/questions/342378/cross-domain-login-how-to-login-a-user-automatically-when-transferred-from-one
 * - https://github.com/markoheijnen/wordpress-mu-domain-mapping/blob/master/domain_mapping.php#L758-L814
 */
function dark_matter_sso_endpoint() {
  add_rewrite_endpoint( 'sso', EP_ALL );
}
add_action( 'init', 'dark_matter_sso_endpoint' );

function dark_matter_sso_plugins_loaded() {
	if ( 'authorise' === filter_input( INPUT_GET, '__dm_action' ) ) {
		$user_id = wp_validate_auth_cookie( filter_input( INPUT_GET, 'auth' ), 'auth' );

		if ( false === $user_id ) {
			wp_die( 'Oops! Something went wrong with logging in.' );
		}
		else {
			wp_set_auth_cookie( $user_id );

			wp_redirect( esc_url( remove_query_arg( array( '__dm_action', 'auth' ) ) ) );
			exit;
		}
	}
}
add_action( 'plugins_loaded', 'dark_matter_sso_plugins_loaded' );

function dark_matter_sso_template() {
  global $wp_query;

  if ( array_key_exists( 'sso', $wp_query->query_vars ) ) {
    header( 'Content-Type: text/javascript' );

	if ( is_user_logged_in() ) {
		$url = add_query_arg( array(
			'__dm_action' => 'authorise',
			'auth' => wp_generate_auth_cookie( get_current_user_id(), time() + ( 2 * MINUTE_IN_SECONDS ) )
		), $_SERVER['HTTP_REFERER'] );

		printf( 'window.location.replace("%s");', esc_url_raw( $url ) );
	}

    exit;
  }
}
add_action( 'template_redirect', 'dark_matter_sso_template' );

function dark_matter_sso_wp_head() {
	if ( false === is_user_logged_in() && 1 < get_current_blog_id() ) : ?>
	<script type="text/javascript" src="<?php echo( network_site_url( '/sso/' ) ); ?>"></script>
<?php endif;
}
add_action( 'wp_head', 'dark_matter_sso_wp_head' );

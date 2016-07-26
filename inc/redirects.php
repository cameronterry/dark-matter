<?php

defined( 'ABSPATH' ) or die();

function dark_matter_admin_redirect() {
	/**
	 * Do not redirect the AJAX calls. It is part of the admin but not
	 * the part we wish to put redirects on.  Especially for Front-end
	 * AJAX functionality.
	 */
	if ( false !== strpos( $_SERVER['REQUEST_URI'], 'wp-admin/admin-ajax.php' ) ) {
		return;
	}

	/**
	 * Also do not redirect the Cron URL.
	 */
	if ( false !== strpos( $_SERVER['REQUEST_URI'], 'wp-cron.php' ) ) {
		return;
	}

	global $current_blog;
	$original_domain = dark_matter_api_get_domain_original();

	if ( false === empty( $original_domain ) && false === strpos( $original_domain, $_SERVER[ 'HTTP_HOST' ] ) ) {
		$protocol = ( is_ssl() ? 'https://' : 'http://' );
		$protocol = ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https://' : $protocol );

		$domain = untrailingslashit( $original_domain );
		$request = $_SERVER['REQUEST_URI'];

		wp_redirect( sprintf( '%1$s%2$s%3$s', $protocol, $domain, $request ) );
		exit;
	}
}
add_action( 'admin_init', 'dark_matter_admin_redirect' );
add_action( 'login_init', 'dark_matter_admin_redirect' );

function dark_matter_frontend_redirect() {
	/**
	 * If it's the main site in the network, do not redirect. Also double-check
	 * to make sure this isn't called in the admin area as parse_request action
	 * is used both back-end and front-end.
	 */
	if ( is_admin() || is_main_site() || is_preview() || in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) || defined( 'XMLRPC_REQUEST' ) ) {
		return;
	}

	/** If we are viewing in the WP Customizer, then we don't want to redirect either. */
	global $wp_customize;

	if ( is_a( $wp_customize, 'WP_Customize_Manager' ) ) {
		return;
	}

	global $current_blog, $wpdb;

	$primary_domain = dark_matter_api_get_domain_primary();

	/** No domain has been mapped. */
	if ( empty( $primary_domain ) ) {
		return;
	}

	$domains_match = ( $primary_domain === $_SERVER['HTTP_HOST'] );
	$protocols_match = ( $current_blog->https === array_key_exists( 'HTTPS', $_SERVER ) );

	if ( $domains_match && $protocols_match ) {
		return;
	}

	/**
	 * If the domains do not match, then we work on the assumption that a large
	 * portion of the URL or the whole thing needs reworked to be the primary
	 * domain.
	 */
	if ( false === $domains_match ) {
		$scheme = ( $current_blog->https ? 'https://' : 'http://' );
		$path = '';

		/**
		 * Check to make sure Path is not just a forward slash and handle the
		 * URL reconstruction appropriately.
		 */
		if ( '/' === $current_blog->path ) {
			$path = $_SERVER['REQUEST_URI'];
		}
		else {
			$path = str_replace( $current_blog->path, '', $_SERVER['REQUEST_URI'] );
		}

		/** Construct the final redirect URL with the primary domain. */
		$redirect_url = sprintf( '%1$s%2$s/%3$s', $scheme, $primary_domain, $path );
	}
	else if ( false === $protocols_match ) {
		/**
		 * Someone has attempted to access the URL on the HTTP version of the blog
		 * and it is currently set to accept only HTTPS (or vice versa). Then this
		 * handles that redirect.
		 */
		$redirect_url = sprintf( '%1$s://%2$s%3$s', ( $current_blog->https ? 'https' : 'http' ), $primary_domain, $_SERVER['REQUEST_URI'] );
	}

	wp_redirect( $redirect_url );
	return;
}
add_action( 'parse_query', 'dark_matter_frontend_redirect' );

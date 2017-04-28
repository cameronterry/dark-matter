<?php

defined( 'ABSPATH' ) or die();

function dark_matter_redirect_url( $domain, $is_https ) {
	global $current_blog;

	/** No domain has been mapped. */
	if ( empty( $domain ) ) {
		return false;
	}

	/** We ensure we have a clean domain and that there is no trailing slash. */
	$domain = untrailingslashit( $domain );
	$request = strtok( $_SERVER['REQUEST_URI'], '?' );

	/**
	 * Handle the query string parameters. This is used to reassemble the URL
	 * later for the redirect.
	 */
	$querystring = '';

	if ( array_key_exists( 'QUERY_STRING', $_SERVER ) && false === empty( $_SERVER['QUERY_STRING'] ) ) {
		$querystring = '?' . $_SERVER['QUERY_STRING'];
	}

	/** Now performance the checks on the domain and protocol. */
	$domains_match = ( strtolower( $domain ) === strtolower( $_SERVER['HTTP_HOST'] ) );
	$protocols_match = true;

	if ( $is_https ) {
		$protocols_match = ( array_key_exists( 'HTTPS', $_SERVER ) && ( $_SERVER['HTTPS'] || 'on' === $_SERVER['HTTPS'] ) );
	}

	if ( $domains_match && $protocols_match ) {
		return false;
	}

	$redirect_url = null;
	$scheme = ( $is_https ? 'https://' : 'http://' );

	/**
	 * If the domains do not match, then we work on the assumption that a large
	 * portion of the URL or the whole thing needs reworked to be the primary
	 * domain.
	 */
	if ( false === $domains_match ) {
		/**
		 * Check to make sure Path is not just a forward slash and handle the
		 * URL reconstruction appropriately.
		 */
		if ( '/' === $current_blog->path ) {
			$path = $request;
		}
		else {
			$path = str_replace( $current_blog->path, '', $request );
		}

		if ( trailingslashit( $_SERVER['HTTP_HOST'] . $request ) === $current_blog->domain . $current_blog->path ) {
			$path = '/';
		}

		if ( '/' !== substr( $path, 0, 1 ) ) {
			$path = '/' . $path;
		}

		if ( '.php' !== substr( $path, -4 ) ) {
			$path = trailingslashit( $path );
		}

		/** Construct the final redirect URL with the primary domain. */
		$redirect_url = sprintf( '%1$s%2$s%3$s', $scheme, $domain, $path . $querystring );
	}
	else if ( false === $protocols_match ) {
		if ( '/' !== substr( $request, 0, 1 ) ) {
			$request = '/' . $request;
		}

		/**
		 * Someone has attempted to access the URL on the HTTP version of the blog
		 * and it is currently set to accept only HTTPS (or vice versa). Then this
		 * handles that redirect.
		 */
		$redirect_url = sprintf( '%1$s%2$s%3$s', $scheme, $domain, trailingslashit( $request ) . $querystring );
	}

	if ( empty( $redirect_url ) ) {
		return false;
	}

	return $redirect_url;
}

function dark_matter_main_redirect() {
	/** If Preview, then exit and let the wp action hook handle it. */
	if ( array_key_exists( 'preview', $_GET ) || array_key_exists( 'p', $_GET ) ) {
		return;
	}

	/** If no domain has been mapped, then exit as well. */
	$primary_domain = dark_matter_api_get_domain_primary();
	if ( empty( $primary_domain ) ) {
		return;
	}

	/** Do not redirect the Cron URL. */
	if ( defined( 'DOING_CRON' ) ) {
		return;
	}

	/** Also do not redirect for the version 1.x.x of the WP REST API. */
	if ( defined( 'JSON_API_VERSION' ) && ( false !== strpos( $_SERVER['REQUEST_URI'], 'wp-json/' ) || ( defined( 'JSON_REQUEST' ) && JSON_REQUEST ) ) ) {
		return;
	}

	/** If a request on XML-RPC, then also exit. */
	if ( defined( 'XMLRPC_REQUEST' ) ) {
		return;
	}

	/** If a request for WP Customizer, then also exit. */
	global $wp_customize;
	if ( is_a( $wp_customize, 'WP_Customize_Manager' ) ) {
		return;
	}

	/**
	 * If it's the main site in the network, do not redirect. Also double-check
	 * to make sure this isn't called in the admin area as parse_request action
	 * is used both back-end and front-end.
	 */
	global $current_blog, $pagenow;

	/**
	 * Unlike before where we had differnet redirect hooks for the admin and
	 * front-end, this new implementation combines both.
	 */
	if ( is_admin() || in_array( $pagenow, array( 'wp-login.php', 'wp-register.php' ) ) ) {
		/** Do not redirect AJAX requests. */
		if ( false !== strpos( $_SERVER['REQUEST_URI'], 'wp-admin/admin-ajax.php' ) ) {
			return;
		}

		/** Also do not redirect the Cron URL. */
		if ( false !== strpos( $_SERVER['REQUEST_URI'], 'wp-cron.php' ) ) {
			return;
		}

		$original_domain = dark_matter_api_get_domain_original();
		$redirect = dark_matter_redirect_url( $original_domain, defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN || is_ssl() );

		if ( false !== $redirect ) {
			wp_redirect( $redirect, 301 );
			exit;
		}
	}
	else {
		/** Front-end redirects. */
		$redirect = dark_matter_redirect_url( $primary_domain, $current_blog->https );

		if ( false !== $redirect ) {
			wp_redirect( $redirect, 301 );
			exit;
		}
	}
}
add_action( 'plugins_loaded', 'dark_matter_main_redirect', 100 );

/**
 * The helper API, is_preview(), can only property assert if the current request
 * is a "preview" after parse_query is executed and the property is set. Prior
 * to this is_preview() will return false but not necessarily because the
 * request is a preview or not, but because the logic has not been executed yet.
 *
 * So to ensure that the Preview is on the Admin domain, we have to use the wp
 * action hook to perform our check and any necessary redirects.
 */
function dark_matter_preview_redirect() {
	if ( is_preview() ) {
		$original_domain = dark_matter_api_get_domain_original();

		if ( false === empty( $original_domain ) ) {
			global $current_blog;
			$redirect = dark_matter_redirect_url( $original_domain, $current_blog->https );

			if ( false !== $redirect ) {
				wp_redirect( $redirect, 301 );
				exit;
			}
		}
	}
}
add_action( 'wp', 'dark_matter_preview_redirect' );

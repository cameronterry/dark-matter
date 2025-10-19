<?php
/**
 * Helper functions used by Dark Matter Plugin features.
 *
 * @package DarkMatterPlugin
 */

namespace DarkMatterPlugin;

/**
 * Get the filename from the request.
 *
 * @return string
 */
function get_request_filename( $request_uri = '' ) {
	$request_uri = ! empty( $request_uri ) ? $request_uri : get_request_uri();
	$request     = ltrim( $request_uri, '/' );

	/**
	 * Get the filename and remove any query strings.
	 */
	$filename = basename( $request );
	return strtok( $filename, '?' );
}

/**
 * Get a sanitized fully qualified domain name for the current request.
 *
 * Note: as this helper function might be used in `advanced-cache.php` drop-in plugin, this is before `formatting.php`
 * which includes functions such as `wp_unslash()` and `wp_strip_all_tags()`.
 *
 * @return string
 */
function get_request_fqdn() {
	$host = ( empty( $_SERVER['HTTP_HOST'] ) ? '' : $_SERVER['HTTP_HOST'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( empty( $host ) ) {
		return $host;
	}

	/**
	 * `HTTP_HOST` can sometimes, incorrectly, include a trailing slash.
	 */
	$host = trim( $host, '/' );

	/**
	 * Fully sanitise the string.
	 */
	return stripslashes_deep( strip_all_tags( $host ) );
}

/**
 * Retrieve a sanitised Request URI.
 *
 * @return string
 */
function get_request_uri() {
	return empty( $_SERVER['REQUEST_URI'] ) ? '' : stripslashes_deep( strip_all_tags( $_SERVER['REQUEST_URI'] ) );
}

/**
 * Checks the supply blog/site to ensure it is public.
 *
 * @param \WP_Site $blog Blog to check.
 * @return bool True if public. False otherwise.
 */
function is_site_public( $blog ) {
	/**
	 * Make sure we have the right kind of object.
	 */
	if ( ! $blog instanceof \WP_Site ) {
		return false;
	}

	return (
		/**
		 * Check the current blog is public and be compatible with plugins such as Restricted Site Access/RSA, which
		 * may set this value lower than zero (0) for differentiating between settings.
		 */
		0 < (int) $blog->public
		/**
		 * Has the blog/"site" been archived?
		 */
		&& 0 === (int) $blog->archived
		/**
		 * Has the blog/"site" been "soft" deleted?
		 */
		&& 0 === (int) $blog->deleted
	);
}

/**
 * A simplified version of `wp_strip_all_tags()` that can be used without manually including `formatting.php`.
 *
 * @see wp_strip_all_tags()
 *
 * @param string $text Text to be sanitised.
 * @return string
 */
function strip_all_tags( $text ) {
	if ( empty( $text ) ) {
		return '';
	}

	$text = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $text );
	$text = strip_tags( $text );

	$text = preg_replace( '/[\r\n\t ]+/', ' ', $text );

	return trim( $text );
}

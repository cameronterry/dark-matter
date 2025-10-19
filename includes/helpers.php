<?php
/**
 * Helper functions used by Dark Matter Plugin features.
 *
 * @package DarkMatterPlugin
 */

namespace DarkMatterPlugin;

/**
 * Get the, sanitized, fully qualified domain name for the current request.
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

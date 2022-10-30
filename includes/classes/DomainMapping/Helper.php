<?php
/**
 * Helper methods.
 *
 * @package DarkMatter\DomainMapping
 */

namespace DarkMatter\DomainMapping;

use DarkMatter\DomainMapping\Manager\Primary;

/**
 * Class Helpers
 *
 * @since 3.0.0
 */
class Helper {
	/**
	 * Map the primary domain on the passed in value if it contains the unmapped URL and the Site has a primary domain.
	 *
	 * @since 3.0.0
	 *
	 * @param  mixed   $value   Potentially a value containing the site's unmapped URL.
	 * @param  integer $blog_id Site (Blog) ID for the URL which is being mapped.
	 * @return string           If unmapped URL is found, then returns the primary URL. Untouched otherwise.
	 */
	public function map( $value = '', $blog_id = 0 ) {
		/**
		 * Ensure that we are working with a string.
		 */
		if ( ! is_string( $value ) ) {
			return $value;
		}

		/**
		 * Retrieve the current blog.
		 */
		$blog    = get_site( absint( $blog_id ) );
		$primary = Primary::instance()->get( $blog->blog_id );

		$unmapped = untrailingslashit( $blog->domain . $blog->path );

		/**
		 * If there is no primary domain or the unmapped version cannot be found
		 * then we return the value as-is.
		 */
		if ( empty( $primary ) || false === stripos( $value, $unmapped ) ) {
			return $value;
		}

		$domain = 'http' . ( $primary->is_https ? 's' : '' ) . '://' . $primary->domain;

		return preg_replace( "#https?://{$unmapped}#", $domain, $value );
	}

	/**
	 * Converts a URL from a mapped domain to the admin domain. This will only convert a URL which is the primary
	 * domain.
	 *
	 * @since 3.0.0
	 *
	 * @param  mixed $value Potentially a value containing the site's unmapped URL.
	 * @return mixed        If unmapped URL is found, then returns the primary URL. Untouched otherwise.
	 */
	public function unmap( $value = '' ) {
		/**
		 * Ensure that we are working with a string.
		 */
		if ( ! is_string( $value ) ) {
			return $value;
		}

		/**
		 * Retrieve the current blog.
		 */
		$blog    = get_site();
		$primary = Primary::instance()->get();

		/**
		 * If there is no primary domain or the primary domain cannot be found
		 * then we return the value as-is.
		 */
		if ( empty( $primary ) || false === stripos( $value, $primary->domain ) ) {
			return $value;
		}

		$unmapped = 'http' . ( $primary->is_https ? 's' : '' ) . '://' . untrailingslashit( $blog->domain . $blog->path );

		return preg_replace( "#https?://{$primary->domain}#", $unmapped, $value );
	}

	/**
	 * Return the Singleton Instance of the class.
	 *
	 * @return Helper
	 */
	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}

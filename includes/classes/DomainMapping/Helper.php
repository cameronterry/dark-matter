<?php
/**
 * Helper methods.
 *
 * @since 3.0.0
 *
 * @package DarkMatterPlugin\DomainMapping
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
	 * Check the supplied domain to ensure it is correct.
	 *
	 * @param string $domain Domain to check.
	 * @return string|\WP_Error
	 */
	public function check_domain( $domain ) {
		if ( empty( $domain ) ) {
			return new \WP_Error( 'empty', __( 'Please include a fully qualified domain name to be added.', 'dark-matter' ) );
		}

		/**
		 * Ensure that the URL is purely a domain. In order for the parse_url() to work, the domain must be prefixed
		 * with a double forward slash.
		 */
		if ( false === stripos( $domain, '//' ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			$domain_parts = parse_url( '//' . ltrim( $domain, '/' ) );
		} else {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			$domain_parts = parse_url( $domain );
		}

		if ( ! empty( $domain_parts['path'] ) || ! empty( $domain_parts['port'] ) || ! empty( $domain_parts['query'] ) ) {
			return new \WP_Error( 'unsure', __( 'The domain provided contains path, port, or query string information. Please removed this before continuing.', 'dark-matter' ) );
		}

		$domain = $domain_parts['host'];

		/**
		 * Check to ensure we have a valid domain to work with.
		 */
		if ( ! filter_var( $domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME ) ) {
			return new \WP_Error( 'domain', __( 'The domain is not valid.', 'dark-matter' ) );
		}

		return $domain;
	}

	/**
	 * Retrieves the FQDN as request from the URL.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_request_fqdn() {
		$host = ( empty( $_SERVER['HTTP_HOST'] ) ? '' : $_SERVER['HTTP_HOST'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return trim( $host, '/' );
	}

	/**
	 * Get the filename from the request.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_request_filename() {
		$request_uri = ( empty( $_SERVER['REQUEST_URI'] ) ? '' : wp_strip_all_tags( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
		$request     = ltrim( $request_uri, '/' );

		/**
		 * Get the filename and remove any query strings.
		 */
		$filename = basename( $request );
		return strtok( $filename, '?' );
	}

	/**
	 * Determines if the request is admin, but unlike the `is_admin()`, will also check for the login and register page.
	 *
	 * @since 3.0.0
	 *
	 * @param string $filename Filename.
	 * @return bool True if admin, false otherwise.
	 */
	public function is_admin( $filename = '' ) {
		$filename = empty( $filename ) ? $this->get_request_filename() : $filename;

		$admin_filenames = [
			'wp-login.php'    => true,
			'wp-register.php' => true,
		];

		if ( is_admin() || ( ! empty( $filename ) && array_key_exists( $filename, $admin_filenames ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks the supply blog/site to ensure it is public.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Site $blog Blog to check.
	 * @return bool True if public. False otherwise.
	 */
	public function is_public( $blog ) {
		/**
		 * Make sure we have the right kind of object.
		 */
		if ( ! $blog instanceof \WP_Site ) {
			return false;
		}

		return (
			/**
			 * Check the current blog is public and be compatible with plugins such as Restricted Site Access.
			 */
			0 < (int) $blog->public
			/**
			 * Check the current blog has not been archived.
			 */
			&& 0 === (int) $blog->archived
			/**
			 * Check the current blog has not been "soft" deleted.
			 */
			&& 0 === (int) $blog->deleted
		);
	}

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

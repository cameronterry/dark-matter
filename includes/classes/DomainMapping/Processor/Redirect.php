<?php
/**
 * Processor for determining if a redirect is required.
 *
 * @since 3.0.0
 *
 * @package DarkMatterPlugin\Domain
 */

namespace DarkMatter\DomainMapping\Processor;

use DarkMatter\Interfaces\Registerable;

/**
 * Class Redirect
 *
 * @since 3.0.0
 */
class Redirect implements Registerable {
	/**
	 * Determine if the current request is something we consider for redirecting.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True to redirect, false otherwise.
	 */
	private function can_redirect() {
		/**
		 * The function `rest_get_url_prefix()` is not available at this point in the load process. Therefore, we must
		 * substitute it with a close approximate of what the function does.
		 *
		 * There is a side effect of this. Basically if a site is to be setup with a different prefix, in order for this
		 * to work, the `add_filter()` call would need to be done in a Must-Use Plugin.
		 *
		 * @see https://developer.wordpress.org/reference/hooks/rest_url_prefix/
		 */
		$rest_url_prefix = '/' . trim( apply_filters( 'rest_url_prefix', 'wp-json' ), '/' ) . '/';

		return ! (
			/**
			 * Do not attempt to redirect during the CLI command.
			 */
			( defined( 'WP_CLI' ) && WP_CLI )
			||
			/**
			 * AJAX requests can be used on both the mapped and unmapped domains.
			 */
			( defined( 'DOING_AJAX' ) && DOING_AJAX )
			||
			/**
			 * Do not attempt to redirect during the execution of cron.
			 */
			( defined( 'DOING_CRON' ) && DOING_CRON )
			||
			/**
			 * XMLRPC Requests can be used on both the mapped and unmapped domains.
			 */
			( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
			||
			/**
			 * REST API can be used on both the mapped and unmapped domains.
			 */
			( ! empty( $_SERVER['REQUEST_URI'] ) && false !== strpos( $_SERVER['REQUEST_URI'], $rest_url_prefix ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			||
			/**
			 * Customizer is presented in an <iframe> over the unmapped domain.
			 */
			! empty( $_GET['customize_changeset_uuid'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			||
			/**
			 * Do not redirect Previews
			 */
			( ! empty( $_GET['preview'] ) || ! empty( $_GET['page_id'] ) || ! empty( $_GET['p'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		);
	}

	/**
	 * Determines if the current request is an admin one.
	 *
	 * @since 3.0.0
	 *
	 * @param string $filename Filename.
	 * @return bool True if admin request, false otherwise.
	 */
	private function is_admin( $filename = '' ) {
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
	 * Check to see if the current request is an Admin Post action or an AJAX action. These two requests in Dark Matter
	 * can be on either the admin domain or the primary domain.
	 *
	 * @since 3.0.0
	 *
	 * @param string $filename Filename.
	 * @return bool True if request is AJAX, false otherwise.
	 */
	private function is_ajax( $filename = '' ) {
		$ajax_filenames = [
			'admin-post.php' => true,
			'admin-ajax.php' => true,
		];


		if ( ! empty( $filename ) && array_key_exists( $filename, $ajax_filenames ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Performs various checks and, if required, performs the redirect.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function maybe_redirect() {
		/**
		 * Do not perform redirects if it is the main site.
		 *
		 * Note: this is here inside the caller on `muplugins_loaded` as earlier is before the function is available for
		 * use.
		 */
		if ( is_main_site() ) {
			return;
		}

		$request_uri = ( empty( $_SERVER['REQUEST_URI'] ) ? '' : filter_var( $_SERVER['REQUEST_URI'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW ) );
		$request     = ltrim( $request_uri, '/' );

		/**
		 * Get the filename and remove any query strings.
		 */
		$filename = basename( $request );
		$filename = strtok( $filename, '?' );

		if ( $this->is_ajax( $filename ) ) {
			return;
		}

		$is_admin = $this->is_admin( $filename );
	}

	/**
	 * Register hooks for this class.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register() {
		if ( $this->can_redirect() ) {
			/**
			 * We use `muplugins_loaded` action (introduced in WordPress 2.8.0) rather than
			 * the "ms_loaded" (introduced in WordPress 4.6.0).
			 *
			 * A hook on `muplugins_loaded` is used to ensure that WordPress has loaded the
			 * Blog / Site globals. This is specifically useful when some one goes to the
			 * Admin domain URL - http://my.sites.com/two/ - which is to redirect to the
			 * primary domain - http://example.com.
			 */
			add_action( 'muplugins_loaded', [ $this, 'maybe_redirect' ], 20 );
		}
	}
}

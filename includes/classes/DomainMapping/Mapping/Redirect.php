<?php
/**
 * Determine if the current request should be redirected (primarily secondary domains, but also other scenarios).
 *
 * @package DarkMatterPlugin
 */

namespace DarkMatterPlugin\DomainMapping\Mapping;

use DarkMatterPlugin\Registerable;
use function DarkMatterPlugin\get_request_filename;
use function DarkMatterPlugin\get_request_fqdn;
use function DarkMatterPlugin\get_request_uri;
use function DarkMatterPlugin\is_site_public;

/**
 * Class Redirect
 */
class Redirect implements Registerable {

	/**
	 * Can this class register and should the current request be checked for redirects.
	 *
	 * @return bool
	 */
	public function can_register() {
		/**
		 * The function `rest_get_url_prefix()` is not available at this point in the load process. Therefore, we must
		 * substitute it with a close approximate of what the function does.
		 *
		 * There is a side effect of this. Basically if a site is to be setup with a different prefix, in order for this
		 * to work, the `add_filter()` call would need to be done in a Must-Use Plugin.
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
	 * Is the current request for the `/wp-admin/`.
	 *
	 * @param string $request_filename Filename for the current request.
	 * @return bool
	 */
	private function is_admin( $request_filename ) {
		$admin_filenames = [
			'wp-login.php'    => true,
			'wp-register.php' => true,
		];

		return (
			is_admin()
			|| ( ! empty( $request_filename ) && array_key_exists( $request_filename, $admin_filenames ) )
		);
	}

	/**
	 * Check to see if the current request is an Admin Post action or an AJAX action. These two requests in Dark Matter
	 * can be on either the admin domain or the primary domain.
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
	 * Run the various checks for the Redirect logic and, if necessary, perform the redirect.
	 *
	 * @return void
	 */
	public function maybe_redirect() {
		/**
		 * Do not perform redirects if it is the main site nor a non-public site.
		 */
		$original_blog = get_site();
		if (
			! $original_blog instanceof \WP_Site
			|| is_main_site()
			|| ! is_site_public( $original_blog )
		) {
			return;
		}

		$request_uri      = ltrim( get_request_uri(), '/' );
		$request_filename = get_request_filename( $request_uri );

		/**
		 * Ignore AJAX (Dark Matter Plugin's definition) requests.
		 */
		if ( $this->is_ajax( $request_filename ) ) {
			return;
		}

		$fqdn     = get_request_fqdn();
		$is_admin = $this->is_admin( $request_filename );

		/**
		 * If Allow Logins is enabled, then the `wp-login.php` request is to be made available on both the primary
		 * domain and admin domain (i.e. WooCommerce).
		 */
		if (
			! apply_filters( 'darkmatter_allow_logins', false )
			&& $is_admin
			&& $fqdn === $original_blog->domain
		) {
			return;
		}

		/**
		 * If there is no primary domain, there is nothing to do. Also make sure the domain is active.
		 */
		$primary = \DarkMatter_Primary::instance()->get();
		if ( ! $primary || ! $primary->active || absint( $original_blog->public ) < 1 ) {
			return;
		}

		if ( $is_admin && $fqdn !== $original_blog->domain ) {
			$is_ssl_admin = ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN );

			$url = 'http' . ( $is_ssl_admin ? 's' : '' ) . '://' . $original_blog->domain . $original_blog->path . $request_uri;
		} elseif ( $fqdn !== $primary->domain || is_ssl() !== $primary->is_https ) {
			$url = 'http' . ( $primary->is_https ? 's' : '' ) . '://' . $primary->domain . '/' . $request_uri;

			/**
			 * Make sure the Path - if this is a sub-folder Network - is removed from the URL. For subdomain Networks,
			 * the path will be a single forward slash (/).
			 */
			if ( '/' !== $original_blog->path ) {
				$path = '/' . trim( $original_blog->path, '/' ) . '/';
				$url  = str_ireplace( $path, '/', $url );
			}
		}

		/**
		 * If the URL is empty, then there is no redirect to perform.
		 */
		if ( empty( $url ) ) {
			return;
		}

		header( 'X-Redirect-By: DarkMatterPlugin' );
		header( 'Location:' . $url, true, 301 );

		die;
	}

	/**
	 * Handle actions and filters for Redirect logic.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'plugins_loaded', [ $this, 'maybe_redirect' ], 20 );
	}
}

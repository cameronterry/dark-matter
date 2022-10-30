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
	 * Register hooks for this class.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register() {
		if ( $this->can_redirect() ) {
			// todo add_action( 'muplugins_loaded', 'darkmatter_maybe_redirect', 20 );
		}
	}
}

<?php
/**
 * Handle the logic for making the SSO automatic.
 *
 * @package DarkMatterPlugin
 */

namespace DarkMatter\SSO;

use DarkMatter\Interfaces\Registerable;

/**
 * Class Script
 */
class Script implements Registerable {
	/**
	 * Admin script to create the localStorage variables.
	 *
	 * @return void
	 */
	public function admin() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$token_api = new Token( 'dmp_auth_token', 'dark-matter-plugin-authtokens', true );

		$token_id = $token_api->get( get_current_user_id() );
		if ( empty( $token_id ) ) {
			return;
		}

		$token_data = $token_api->get( $token_id, 'token' );
		if ( empty( $token_data ) ) {
			return;
		}

		?>
		<script defer>
			( function () {
				if ( ! window.localStorage ) {
					return;
				}

				window.localStorage.setItem( 'dmpTokenId', <?php echo wp_json_encode( $token_id ); ?> );
				window.localStorage.setItem( 'dmpTokenNonce', <?php echo wp_json_encode( $token_data['nonce'] ); ?> );
			} )();
		</script>
		<?php
	}

	/**
	 * Use `admin-post.php?action=` convention which is the endpoint used for the `<iframe>` on the primary domain side
	 * of the SSO.
	 *
	 * @return void
	 */
	public function iframe_endpoint() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$domain = untrailingslashit( home_url() );

		header(
			sprintf( 'Content-Security-Policy: frame-ancestors %s', $domain )
		);

		/**
		 * Very basic HTML output, which is done to ensure the browser doesn't accidentally do something weird.
		 */
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
			<head>
				<title><?php echo esc_html( wp_get_document_title() ); ?></title>
			</head>
			<body>
				<!-- <?php esc_html_e( 'This request is used as part of the SSO function for Dark Matter Plugin.', 'dark-matter-plugin' ); ?> -->
				<script>
					( function () {
						if ( ! window.localStorage ) {
							return;
						}

						var tokenId = window.localStorage.getItem( 'dmpTokenId' );
						var tokenNonce = window.localStorage.getItem( 'dmpTokenNonce' );
						if ( tokenId && tokenNonce ) {
							window.postMessage(
								{
									tokenId,
									tokenNonce
								},
									<?php echo wp_json_encode( home_url( '/' ) ); ?>
							);
						}
					} )();
				</script>
			</body>
		</html>
		<?php
	}

	/**
	 * Handle actions and filters for SSO JavaScript.
	 *
	 * @return void
	 */
	public function register() {
		/**
		 * Admin-side.
		 */
		add_action( 'admin_footer', [ $this, 'admin' ] );

		/**
		 * Visitor-side.
		 */
		add_action( 'admin_post_nopriv_dmpsso', [ $this, 'iframe_endpoint' ], 2000 );
		add_action( 'admin_post_dmpsso', [ $this, 'iframe_endpoint' ], 2000 );
		add_action( 'wp_footer', [ $this, 'visitor' ], 2000 );
	}

	/**
	 * Handle JS for visitor side.
	 *
	 * @return void
	 */
	public function visitor() {
		/**
		 * Already done.
		 */
		if ( is_user_logged_in() ) {
			return;
		}
		?>
		<script defer>
			( function () {
				window.addEventListener(
					'message',
					function (evt) {
						if ( evt.origin !== <?php echo wp_json_encode( admin_url() ); ?> ) {
							return;
						}

						console.log( evt.data );
					},
					false );
			} )();
		</script>
		<?php
	}
}

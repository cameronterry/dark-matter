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

				window.localStorage.setItem( 'dmp_token_id', <?php echo wp_json_encode( $token_id ); ?> );
				window.localStorage.setItem( 'dmp_token_nonce', <?php echo wp_json_encode( $token_data['nonce'] ); ?> );
			} )();
		</script>
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
		add_action( 'wp_footer', [ $this, 'iframe' ], 2000 );
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

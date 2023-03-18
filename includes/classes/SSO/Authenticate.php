<?php
/**
 * Authentication for SSO.
 *
 * @package DarkMatterPlugin\SSO
 */

namespace DarkMatter\SSO;

use DarkMatter\Interfaces\Registerable;
use function DarkMatter\Functions\verify_nonce;

/**
 * Class Authenticate
 */
class Authenticate implements Registerable {
	/**
	 * Token API.
	 *
	 * @var null|Token
	 */
	private $token_api = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->token_api = new Token( 'dmp_login_token' );
	}

	/**
	 * Retrieve the URL data.
	 *
	 * @return array|false|null
	 */
	private function get_data() {
		return filter_var_array(
			$_GET,
			[
				'action' => [
					'filter'  => FILTER_CALLBACK,
					'options' => 'sanitize_text_field',
				],
				'nonce'  => [
					'filter'  => FILTER_CALLBACK,
					'options' => 'sanitize_text_field',
				],
				'token'  => [
					'filter'  => FILTER_CALLBACK,
					'options' => 'sanitize_text_field',
				],
			]
		);
	}

	/**
	 * Prepare a redirect to the mapped domain which can be used for authentication and log on.
	 *
	 * @param string $token_id   Token ID.
	 * @param array  $token_data Data from the Token that completed the authorisation.
	 * @return void
	 */
	public function prepare( $token_id, $token_data ) {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id = get_current_user_id();
		$nonce   = wp_create_nonce( sprintf( 'dmp_login_check_%s', $user_id ) );

		$data = [
			'nonce'         => $nonce,
			'session_token' => wp_get_session_token(),
			'user_id'       => $user_id,
		];

		// Create a new token with User ID in the data.
		$token_id = $this->token_api->create( $user_id, '', $data );

		$url = add_query_arg(
			[
				'action' => 'dmp_auth_do',
				'token'  => $token_id,
				'nonce'  => $nonce,
				't'      => time(), // Cache buster
			],
			home_url( '/' )
		);

		// Redirect to the mapped domain to log them in.
		wp_safe_redirect( $url, 302, 'DarkMatterPlugin' );
	}

	/**
	 * Handle actions and filters for SSO Authenticate.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'darkmatterplugin.sso.authorised', [ $this, 'prepare' ], 10, 2 );

		// Add action to log in the user.
		add_action( 'template_redirect', [ $this, 'signin' ] ); // TODO: Should probably be a custom action from DMP when a mapped request has been determined.
	}

	/**
	 * Perform sign-in request.
	 *
	 * @return void
	 */
	public function signin() {
		$data = $this->get_data();

		if ( 'dmp_auth_do' !== $data['action'] ) {
			return;
		}

		/**
		 * Check to see if the token exists.
		 */
		$token_data = $this->token_api->get( $data['token'], 'token' );
		if ( empty( $token_data['nonce'] ) && empty( $token_data['user_id'] ) ) {
			return;
		}

		/**
		 * Check the nonce matches.
		 */
		if ( $data['nonce'] !== $token_data['nonce'] ) {
			return;
		}

		wp_set_auth_cookie( $token_data['user_id'], false, '', $token_data['session_token'] );
		wp_set_current_user( $token_data['user_id'] );

		// Verify nonce doesn't work because the above does not update $_COOKIE.

		$verify_nonce = verify_nonce(
			$token_data['nonce'],
			sprintf( 'dmp_login_check_%s', $token_data['user_id'] ),
			$token_data['user_id'],
			$token_data['session_token']
		);

		/**
		 * With the person signed in, perform an actual verification on the nonce. If it fails, then immediately clear
		 * the auth cookies.
		 */
		if ( ! $verify_nonce ) {
			/**
			 * Sadly the `wp_verify_nonce()` does not provide a way to supply the relevant data without the auth cookies
			 * being set. However, this weird "create" and "destroy" immediately approach to cookies.
			 */
			wp_clear_auth_cookie();
			return;
		}

		wp_safe_redirect( home_url( '/' ), 302, 'DarkMatterPlugin' );
		die;
	}
}

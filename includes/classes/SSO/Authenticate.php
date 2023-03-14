<?php
/**
 * Authentication for SSO.
 *
 * @package DarkMatterPlugin\SSO
 */

namespace DarkMatter\SSO;

use DarkMatter\Interfaces\Registerable;

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
			'user_id' => $user_id,
			'nonce'   => $nonce,
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
	}
}

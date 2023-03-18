<?php
/**
 * Handle the SSO authorisation flow.
 *
 * @package DarkMatterPlugin\SSO
 */

namespace DarkMatter\SSO;

use DarkMatter\Interfaces\Registerable;

/**
 * Class Authorise
 */
class Authorise implements Registerable {
	/**
	 * URL data.
	 *
	 * @var array
	 */
	private $data = [];

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
		$this->token_api = new Token( 'dmp_auth_token', 'dark-matter-plugin-authtokens', true );
	}

	/**
	 * Ensure the admin request is valid.
	 *
	 * @return bool True to proceed. False otherwise.
	 */
	private function is_valid_admin() {
		/**
		 * If they are not logged in, then we ignore it.
		 */
		if ( ! is_user_logged_in() ) {
			return false;
		}

		/**
		 * Make sure we are an admin request and that it is
		 */
		if ( wp_doing_ajax() || ! is_admin() ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the URL data.
	 *
	 * @return void
	 */
	private function get_data() {
		$this->data = filter_var_array(
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
	 * Handle the authorise request.
	 *
	 * @return void
	 */
	public function handle() {
		if ( is_user_logged_in() ) {
			return;
		}

		/**
		 * Check to see if the token exists.
		 */
		$token_data = $this->token_api->get( $this->data['token'], 'token' );
		if ( empty( $token_data['nonce'] ) && empty( $token_data['user_id'] ) ) {
			return;
		}

		/**
		 * Check the nonce matches.
		 */
		if ( $this->data['nonce'] !== $token_data['nonce'] ) {
			return;
		}

		$url = admin_url();
		$url = add_query_arg(
			[
				'action' => 'dmp_auth_verify',
				'token'  => $this->data['token'],
				'nonce'  => $this->data['nonce'],
			],
			$url
		);

		wp_safe_redirect( $url, 302, 'Dark-Matter-Plugin' );
		die;
	}

	/**
	 * Initiate the Authorise process by creating a token.
	 *
	 * @return void
	 */
	public function initiate() {
		if ( ! $this->is_valid_admin() ) {
			return;
		}

		$user_id = get_current_user_id();

		/**
		 * Check to see if a Token already exists.
		 */
		$token = $this->token_api->get( $user_id );
		if ( ! empty( $token ) ) {
			return;
		}

		$data = [
			'user_id' => $user_id,
			'nonce'   => wp_create_nonce( sprintf( 'dmp_auth_check_%s', $user_id ) ),
		];

		$this->token_api->create( $user_id, '', $data );
	}

	/**
	 * Handle actions and filters for this SSO Authorise.
	 *
	 * @return void
	 */
	public function register() {
		$this->get_data();

		if ( empty( $this->data['action'] ) ) {
			add_action( 'init', [ $this, 'initiate' ], 11 );
		}

		/**
		 * Check if the current request has data and has the action.
		 */
		if ( ! empty( $this->data ) && 'dmp_auth_check' === $this->data['action'] ) {
			add_action( 'template_redirect', [ $this, 'handle' ] ); // TODO: Should probably be a custom action from DMP when a mapped request has been determined.
		}

		if ( ! empty( $this->data ) && 'dmp_auth_verify' === $this->data['action'] ) {
			add_action( 'init', [ $this, 'verify' ], 11 );
		}
	}

	/**
	 * Verify a request provided by the handle method.
	 *
	 * @return void
	 */
	public function verify() {
		if ( ! $this->is_valid_admin() ) {
			return;
		}

		$user_id = get_current_user_id();

		$token_id = $this->token_api->get( $user_id );
		if ( empty( $token_id ) || $this->data['token'] !== $token_id ) {
			return;
		}

		$token_data = $this->token_api->get( $token_id, 'token' );
		if ( empty( $token_data ) ) {
			return;
		}

		/**
		 * Make sure the Token we are verifying is for the same user as us.
		 */
		if ( $token_data['user_id'] !== $user_id ) {
			return;
		}

		/**
		 * Verify the nonce properly now.
		 *
		 * As this is processed on the admin domain, the person's session is up and running, which will mean the nonce
		 * verify is meaningful.
		 */
		if ( ! wp_verify_nonce( $token_data['nonce'], sprintf( 'dmp_auth_check_%s', $user_id ) ) ) {
			return;
		}

		$this->token_api->delete( $user_id, $token_id );

		/**
		 * Action that is fired when the SSO is authorised with a crafted URL from the front-end.
		 *
		 * @since 3.0.0
		 *
		 * @param string $token_id   Token ID.
		 * @param array  $token_data Data from the Token that completed the authorisation.
		 */
		do_action( 'darkmatterplugin.sso.authorised', $token_id, $token_data );
	}
}

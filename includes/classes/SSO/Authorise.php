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
		/**
		 * Check to see if the token exists.
		 */
		$token_data = Token::instance()->get( $this->data['token'], 'token' );
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
				'action' => 'dmp_auth_check',
				'token'  => $this->data,
			],
			$url
		);

		wp_safe_redirect( $url, 302, 'Dark-Matter-Plugin' );
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
		$token = Token::instance()->get( $user_id );
		if ( ! empty( $token ) ) {
			return;
		}

		Token::instance()->create(
			$user_id,
			'',
			[
				'user_id' => $user_id,
				'nonce'   => wp_create_nonce( sprintf( 'dmp_auth_check_%s', $user_id ) ),
			]
		);
	}

	/**
	 * Handle actions and filters for this SSO Authorise.
	 *
	 * @return void
	 */
	public function register() {
		/**
		 * If they are already logged in, then our work here is done.
		 */
		if ( is_user_logged_in() ) {
			return;
		}

		$this->get_data();

		/**
		 * Check if the current request has data and has the action.
		 */
		if ( empty( $this->data ) || 'dmp_auth_check' === $this->data['action'] ) {
			return;
		}

		add_action( 'template_redirect', [ $this, 'handle' ] ); // TODO: Should probably be a custom action from DMP when a mapped request has been determined.
		add_action( 'init', [ $this, 'initiate' ] );
		add_action( 'init', [ $this, 'verify' ] );
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

		$token_data = Token::instance()->get( $user_id );
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

		/**
		 * Action that is fired when the SSO is authorised with a crafted URL from the front-end.
		 *
		 * @since 3.0.0
		 *
		 * @param array $token_data Data from the Token that completed the authorisation.
		 */
		do_action( 'darkmatterplugin.sso.authorised', $token_data );
	}
}

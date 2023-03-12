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

		add_action( 'template_redirect', [ $this, 'handle' ] );
	}
}

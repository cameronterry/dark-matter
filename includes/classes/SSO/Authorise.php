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
	 * Handle actions and filters for this SSO Authorise.
	 *
	 * @return void
	 */
	public function register() {
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

		/**
		 * Check if the current request has data and has the action.
		 */
		if ( empty( $this->data ) || 'dmp_auth_check' === $this->data['action'] ) {
			return;
		}
	}
}

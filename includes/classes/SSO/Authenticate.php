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
	 * Prepare a redirect to the mapped domain which can be used for authentication and log on.
	 *
	 * @param string $token_id   Token ID.
	 * @param array  $token_data Data from the Token that completed the authorisation.
	 * @return void
	 */
	public function prepare( $token_id, $token_data ) {

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

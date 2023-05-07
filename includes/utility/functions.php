<?php
/**
 * Utility functions.
 *
 * @package DarkMatterPlugin
 */

namespace DarkMatter\Functions;

/**
 * A version of `wp_verify_nonce()` but can be fed a WP User ID and a Session Token, whereas the in-built one relies on
 * getting the information from session cookies.
 *
 * @param string     $nonce   Nonce value that was used for verification, usually via a form field.
 * @param string|int $action  Should give context to what is taking place and be the same when nonce was created.
 * @param int        $user_id User ID.
 * @param string     $token   Session token.
 * @return int|false
 */
function verify_nonce( $nonce, $action = -1, $user_id = 0, $token = '' ) {
	$nonce = (string) $nonce;
	$uid   = (int) $user_id;

	if ( empty( $nonce ) || empty( $user_id ) || empty( $token ) ) {
		return false;
	}

	$user = get_user_by( 'id', $user_id );
	if ( ! $user instanceof \WP_User ) {
		return false;
	}

	$i = wp_nonce_tick( $action );

	// Nonce generated 0-12 hours ago.
	$expected = substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
	if ( hash_equals( $expected, $nonce ) ) {
		return 1;
	}

	// Nonce generated 12-24 hours ago.
	$expected = substr( wp_hash( ( $i - 1 ) . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
	if ( hash_equals( $expected, $nonce ) ) {
		return 2;
	}

	/**
	 * Fires when nonce verification fails.
	 *
	 * @see wp_verify_nonce()
	 */
	do_action( 'wp_verify_nonce_failed', $nonce, $action, $user, $token );

	// Invalid nonce.
	return false;
}

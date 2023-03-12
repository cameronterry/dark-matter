<?php
/**
 * Helper class for the management and storage of tokens.
 *
 * @package DarkMatterPlugin\SSO
 */

namespace DarkMatter\SSO;

/**
 * Class Token
 */
class Token {
	/**
	 * Create a token.
	 *
	 * @param int    $user_id  User ID.
	 * @param string $token_id Token ID. If blank, will auto-generate.
	 * @param array  $data     Array of data to store with the token.
	 * @return false|string Token ID on success. False otherwise.
	 */
	public function create( $user_id, $token_id = '', $data = [] ) {
		if ( ! is_integer( $user_id ) ) {
			return false;
		}

		/**
		 * No Token ID, then generate one.
		 */
		if ( ! empty( $token_id ) ) {
			$token_id = wp_generate_password( 20, false );
		}

		/**
		 * Create an entry so that Token can be found by looking up a WP User ID.
		 */
		wp_cache_set( sprintf( 'dmp_token_for_%s', $user_id ), $token_id, 'dark-matter-plugin', HOUR_IN_SECONDS );

		/**
		 * Create the actual token entry with the appropriate data.
		 */
		wp_cache_set( sprintf( 'dmp_token_%s', $token_id ), $data, 'dark-matter-plugin', HOUR_IN_SECONDS );

		return $token_id;
	}

	/**
	 * Delete a token.
	 *
	 * @param int    $user_id  User ID.
	 * @param string $token_id Token ID.
	 * @return bool True on success. False otherwise.
	 */
	public function delete( $user_id, $token_id ) {
		if ( is_integer( $user_id ) && ! empty( $token_id ) ) {
			wp_cache_delete( sprintf( 'dmp_token_for_%s', $user_id ), 'dark-matter-plugin' );
			wp_cache_delete( sprintf( 'dmp_token_%s', $token_id ), 'dark-matter-plugin' );

			return true;
		}

		return false;
	}

	/**
	 * Retrieve a token by Token ID or User ID.
	 *
	 * @param string $id   ID to retrieve token.
	 * @param string $type ID type; "token" or "user".
	 * @return false|string|array Data from the Token, either as an array or string. Can return false if cache entry has
	 *                            been deleted.
	 */
	public function get( $id, $type = 'user' ) {
		$prefix = [
			'token' => 'dmp_token_',
			'user'  => 'dmp_token_for_',
		];

		if ( ! array_key_exists( $type, $prefix ) ) {
			return [];
		}

		$key = sprintf( '%1$s%2$s', $prefix[ $type ], $id );

		return wp_cache_get( $key, 'dark-matter-plugin' );
	}

	/**
	 * Update a token.
	 *
	 * Note: this will extend the length of time the token will remain.
	 *
	 * @param int   $token_id Token ID.
	 * @param array $data     Data.
	 * @return bool True on success. False otherwise.
	 */
	public function set( $token_id, $data = [] ) {
		if ( empty( $token_id ) ) {
			return false;
		}

		wp_cache_set( sprintf( 'dmp_token_%s', $token_id ), $data, 'dark-matter-plugin', HOUR_IN_SECONDS );

		return true;
	}

	/**
	 * Singleton implementation.
	 *
	 * @return Token
	 */
	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}

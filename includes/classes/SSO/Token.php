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
	 * Token cache group.
	 *
	 * @var string
	 */
	private $cache_group = 'dark-matter-plugin-tokens';

	/**
	 * Token name prefix.
	 *
	 * @var string
	 */
	private $prefix = '';

	/**
	 * Constructor.
	 *
	 * @param string $prefix       Token prefix.
	 * @param string $cache_group  Override the cache group.
	 * @param bool   $cache_global Add the cache group to the global groups, so available across the network.
	 */
	public function __construct( $prefix = '', $cache_group = '', $cache_global = false ) {
		$this->prefix = $prefix ?? 'dmp_token_';

		if ( ! empty( $cache_group ) ) {
			$this->cache_group = $cache_group;
		}

		if ( $cache_global ) {
			wp_cache_add_global_groups( $this->cache_group );
		}
	}

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
		if ( empty( $token_id ) ) {
			$token_id = wp_generate_password( 20, false );
		}

		/**
		 * Create an entry so that Token can be found by looking up a WP User ID.
		 */
		wp_cache_set( sprintf( '%1$sfor_%2$s', $this->prefix, $user_id ), $token_id, $this->cache_group, HOUR_IN_SECONDS );

		/**
		 * Create the actual token entry with the appropriate data.
		 */
		wp_cache_set( sprintf( '%1$s%2$s', $this->prefix, $token_id ), $data, $this->cache_group, HOUR_IN_SECONDS );

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
			wp_cache_delete( sprintf( '%1$sfor_%2$s', $this->prefix, $user_id ), $this->cache_group );
			wp_cache_delete( sprintf( '%1$s%2$s', $this->prefix, $token_id ), $this->cache_group );

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
			'token' => $this->prefix,
			'user'  => sprintf( '%1$sfor_', $this->prefix ),
		];

		if ( ! array_key_exists( $type, $prefix ) ) {
			return [];
		}

		$key = sprintf( '%1$s%2$s', $prefix[ $type ], $id );

		return wp_cache_get( $key, $this->cache_group );
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

		wp_cache_set( sprintf( '%1$s%2$s', $this->prefix, $token_id ), $data, $this->cache_group, HOUR_IN_SECONDS );

		return true;
	}
}
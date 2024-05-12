<?php
/**
 * Handling the custom table which stores the domain mapping information.
 *
 * @package DarkMatterPlugin\DomainMapping\Data
 */

namespace DarkMatter\DomainMapping\Data;

use DarkMatter\Helper\CustomTable;

/**
 * Class DomainMapping
 */
class DomainMapping extends CustomTable {

	/**
	 * Add a domain to the database table.
	 *
	 * @param array $data  Data to be used to create the domain.
	 * @param bool  $force Force the creation. Required if the domain is set to be primary and is to replace the current primary.
	 * @return Domain|false|\WP_Error|null
	 */
	public function add( $data = [], $force = false ) {
		$data = $this->parse_args( $data );
		if ( false === $data ) {
			return false;
		}

		/**
		 * If a domain is supplied, we need to make sure it is valid.
		 */
		if ( ! empty( $data['domain'] ) ) {
			$domain = $this->check_fqdn( $data['domain'] );
			if ( is_wp_error( $domain ) ) {
				return $domain;
			}
		}

		$current_primary = null;
		if ( $data['is_primary'] ) {
			$current_primary = $this->get_primary_domain_id();

			if ( ! $force && ! empty( $current_primary ) ) {
				return new \WP_Error(
					'primary',
					__(
						'You cannot add this domain as the primary domain without using the force flag.',
						'dark-matter'
					)
				);
			}
		}

		$result = parent::add( $data );
		if ( $result ) {
			$domain = new Domain( (object) $data );

			if ( ! empty( $current_primary ) ) {
				$this->update(
					[
						'id'         => $current_primary,
						'is_primary' => false,
					],
					$force
				);

				/**
				 * Fires when a domain is set to be the primary for a Site.
				 *
				 * @since 2.0.0
				 *
				 * @param Domain $domain Domain object.
				 */
				do_action( 'darkmatter_primary_set', $domain );
			}

			/**
			 * Fire action when a domain is added.
			 *
			 * Fires after a domain is successfully added to the database. This
			 * is also post insertion to the cache.
			 *
			 * @since 2.0.0
			 *
			 * @param Domain $domain Domain object of the newly added Domain.
			 */
			do_action( 'darkmatter_domain_add', $domain );

			return $domain;
		}

		return new \WP_Error( 'unknown', __( 'Sorry, the domain could not be added. An unknown error occurred.', 'dark-matter' ) );
	}

	/**
	 * Check to ensure the string domain passes some basic checks.
	 *
	 * @param string $domain Domain to check.
	 * @return \WP_Error|null
	 */
	private function check_fqdn( $domain ) {
		if ( empty( $domain ) ) {
			return new \WP_Error( 'empty', __( 'Please include a fully qualified domain name to be added.', 'dark-matter' ) );
		}

		/**
		 * Ensure that the URL is purely a domain. In order for the parse_url() to work, the domain must be prefixed
		 * with a double forward slash.
		 */
		if ( false === stripos( $domain, '//' ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			$domain_parts = parse_url( '//' . ltrim( $domain, '/' ) );
		} else {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			$domain_parts = parse_url( $domain );
		}

		if ( ! empty( $domain_parts['path'] ) || ! empty( $domain_parts['port'] ) || ! empty( $domain_parts['query'] ) ) {
			return new \WP_Error( 'unsure', __( 'The domain provided contains path, port, or query string information. Please removed this before continuing.', 'dark-matter' ) );
		}

		$domain = $domain_parts['host'];

		/**
		 * Check to ensure we have a valid domain to work with.
		 */
		if ( ! filter_var( $domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME ) ) {
			return new \WP_Error( 'domain', __( 'The domain is not valid.', 'dark-matter' ) );
		}

		if ( defined( 'DOMAIN_CURRENT_SITE' ) && DOMAIN_CURRENT_SITE === $domain ) {
			return new \WP_Error( 'wp-config', __( 'You cannot configure the WordPress Network primary domain.', 'dark-matter' ) );
		}

		if ( is_main_site() ) {
			return new \WP_Error( 'root', __( 'Domains cannot be mapped to the main / root Site.', 'dark-matter' ) );
		}

		$reserve = \DarkMatter\DomainMapping\Manager\Restricted::instance();
		if ( $reserve->is_exist( $domain ) ) {
			return new \WP_Error( 'reserved', __( 'This domain has been reserved.', 'dark-matter' ) );
		}

		/**
		 * Allow for additional checks beyond the in-built Dark Matter ones.
		 *
		 * Ensure that in the case of an error, it should return a WP_Error
		 * object. This is used when providing error messages to the CLI and
		 * REST API endpoints.
		 *
		 * @since 2.0.0
		 *
		 * @param string $domain Fully qualified domain name.
		 * @return string
		 */
		return apply_filters( 'darkmatter_domain_basic_check', $domain );
	}

	/**
	 * Check the domain type to ensure it is supported/valid.
	 *
	 * @param string $type Domain type value to check.
	 * @return true|\WP_Error
	 */
	private function check_type( $type ) {
		/**
		 * Type is either "main" or "media".
		 */
		$types = [
			DM_DOMAIN_TYPE_MAIN,
			DM_DOMAIN_TYPE_MEDIA,
		];

		if ( ! in_array( $type, $types, true ) ) {
			return new \WP_Error( 'type', __( 'The type for the new domain is not supported.', 'dark-matter' ) );
		}

		return true;
	}

	/**
	 * Columns for the Domain Mapping table.
	 *
	 * @return array
	 */
	public function get_columns() {
		return [
			'id'         => [
				'autoincrement' => true,
				'nullable'      => false,
				'queryable'     => true,
				'queryable_in'  => true,
				'type'          => 'BIGINT',
				'type_storage'  => 20,
			],
			'blog_id'    => [
				'nullable'      => false,
				'queryable'     => true,
				'queryable_in'  => true,
				'type'          => 'BIGINT',
				'type_storage'  => 20,
			],
			'is_primary' => [
				'default'       => '0',
				'nullable'      => true,
				'queryable'     => true,
				'type'          => 'TINYINT',
				'type_storage'  => 4,
			],
			'domain'     => [
				'nullable'      => false,
				'queryable'     => true,
				'queryable_in'  => true,
				'type'          => 'VARCHAR',
				'type_storage'  => 255,
			],
			'active'     => [
				'default'       => '1',
				'nullable'      => false,
				'queryable'     => true,
				'type'          => 'TINYINT',
				'type_storage'  => 4,
			],
			'is_https'   => [
				'default'       => '0',
				'nullable'      => true,
				'queryable'     => true,
				'type'          => 'TINYINT',
				'type_storage'  => 4,
			],
			'type'       => [
				'default'       => '1',
				'nullable'      => true,
				'queryable'     => true,
				'type'          => 'TINYINT',
				'type_storage'  => 4,
			],
		];
	}

	/**
	 * Delete a domain.
	 *
	 * @param int  $id    Domain database table ID.
	 * @param bool $force Force the deletion. Required if the domain being deleted is primary.
	 * @return bool|int|\mysqli_result|\WP_Error|null
	 */
	public function delete( $id, $force = false ) {
		$domain = $this->get_record( $id );
		if ( empty( $domain ) ) {
			return false;
		}

		$domain = new Domain( $domain );
		if ( get_current_blog_id() !== $domain->blog_id ) {
			return new \WP_Error( 'not found', __( 'The domain cannot be found.', 'dark-matter' ) );
		}

		if ( $domain->is_primary && ! $force ) {
			return new \WP_Error( 'primary', __( 'This domain is the primary domain for this Site. Please provide the force flag to delete.', 'dark-matter' ) );
		} else {
			/**
			 * Fires when a domain is set to be the primary for a Site.
			 *
			 * @since 2.0.0
			 *
			 * @param Domain $domain Domain object.
			 */
			do_action( 'darkmatter_primary_set', $domain );
		}

		$result = parent::delete( $domain->id );
		if ( $result ) {
			/**
			 * Fire action when a domain is deleted.
			 *
			 * Fires after a domain is successfully deleted to the database.
			 * This is also after the domain is deleted from cache.
			 *
			 * @since 2.0.0
			 *
			 * @param Domain $_domain Domain object that was deleted.
			 */
			do_action( 'darkmatter_domain_delete', $domain );

			return true;
		}

		return new \WP_Error( 'unknown', __( 'Sorry, the domain could not be deleted. An unknown error occurred.', 'dark-matter' ) );
	}

	/**
	 * Not used currently.
	 *
	 * @return false
	 */
	protected function get_indexes() {
		return false;
	}

	/**
	 * Retrieve the primary domain for a blog (i.e. site).
	 *
	 * @param int $blog_id Blog ID.
	 * @return string|null
	 */
	public function get_primary_domain_id( $blog_id = 0 ) {
		$blog_id = empty( $blog_id ) ? get_current_blog_id() : $blog_id;

		global $wpdb;
		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$this->get_tablename()} WHERE is_primary = 1 AND blog_id = %s LIMIT 0, 1",
				$blog_id
			)
		);
	}

	/**
	 * Column for the primary key.
	 *
	 * @return string
	 */
	public function get_primary_key() {
		return 'id';
	}

	/**
	 * Return the domain mapping table name.
	 *
	 * @return string
	 */
	public function get_tablename() {
		global $wpdb;
		return $wpdb->base_prefix . 'domain_mapping';
	}

	/**
	 * Update a domain record.
	 *
	 * @param array $data  Data record to be updated. Is merged with the pre-existing record, only new values need to be included.
	 * @param bool  $force Force update. Set to true when changing a primary domain.
	 * @return bool|Domain|\WP_Error|null
	 */
	public function update( $data = [], $force = false ) {
		/**
		 * Bare minimum needed to update a record.
		 */
		if ( empty( $data['id'] ) ) {
			return false;
		}

		/**
		 * If a domain is supplied, we need to make sure it is valid.
		 */
		if ( ! empty( $data['domain'] ) ) {
			$domain = $this->check_fqdn( $data['domain'] );
			if ( is_wp_error( $domain ) ) {
				return $domain;
			}
		}

		if ( ! empty( $data['type'] ) ) {
			$type_check = $this->check_type( $data['type'] );
			if ( is_wp_error( $type_check ) ) {
				return $type_check;
			}
		}

		$before = $this->get_record( $data['id'] );
		if ( ! empty( $before ) ) {
			$before = new Domain( $before );
		}

		if ( ! $before instanceof Domain ) {
			return false;
		}

		$data = wp_parse_args( $data, $before->to_array() );

		/**
		 * Determine if there is an attempt to update the "is primary" field.
		 */
		$current_primary = null;
		if ( null !== $data['is_primary'] && $data['is_primary'] !== $before->is_primary ) {
			/**
			 * Any update to the "is primary" requires the force flag.
			 */
			if ( ! $force ) {
				return new \WP_Error( 'primary', __( 'You cannot update the primary flag without setting the force parameter to true', 'dark-matter' ) );
			}

			$current_primary = $this->get_primary_domain_id( $data['blog_id'] );
		}

		$result = parent::update( $data );
		if ( $result ) {
			/**
			 * If there was an old primary, then we need to unset it.
			 */
			if ( ! empty( $current_primary ) ) {
				$this->update(
					[
						'id'         => $current_primary,
						'is_primary' => false,
					],
					$force
				);
			}

			$after = new Domain( (object) $data );

			if ( $before->is_primary && ! $after->is_primary ) {
				/**
				 * Fires when a domain is unset to be the primary for a Site.
				 *
				 * @since 2.0.0
				 *
				 * @param Domain $domain Domain object.
				 */
				do_action( 'darkmatter_primary_unset', $after );
			} elseif ( ! $before->is_primary && $after->is_primary ) {
				/**
				 * Fires when a domain is set to be the primary for a Site.
				 *
				 * @since 2.0.0
				 *
				 * @param Domain $domain Domain object.
				 */
				do_action( 'darkmatter_primary_set', $after );
			}

			/**
			 * Fires when a domain is updated.
			 *
			 * @since 2.0.0
			 *
			 * @param Domain $domain_after  Domain object after the changes have been applied successfully.
			 * @param Domain $domain_before Domain object before.
			 */
			do_action( 'darkmatter_domain_updated', $after, $before );

			return $after;
		}

		return false;
	}
}

<?php
/**
 * Class DarkMatter_Domains
 *
 * @package DarkMatter
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || die;

/**
 * Class DarkMatter_Domains
 *
 * @since 2.0.0
 */
class DarkMatter_Domains {
	/**
	 * The Domain Mapping table name for use by the various methods.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	private $dmtable = '';

	/**
	 * Hard-coded media domains, most likely through `DM_NETWORK_MEDIA` constant.
	 *
	 * @var array
	 */
	public $network_media = [];

	/**
	 * Reference to the global $wpdb and is more for code cleaniness.
	 *
	 * @since 2.0.0
	 *
	 * @var boolean
	 */
	private $wpdb = false;

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		global $wpdb;

		/**
		 * Setup the table name for use throughout the methods.
		 */
		$this->dm_table = $wpdb->base_prefix . 'domain_mapping';

		/**
		 * Store a reference to $wpdb as it will be used a lot.
		 */
		$this->wpdb = $wpdb;

		if ( defined( 'DM_NETWORK_MEDIA' ) && ! empty( DM_NETWORK_MEDIA ) ) {
			$this->network_media = DM_NETWORK_MEDIA;
		}
	}

	/**
	 * Perform basic checks before committing to a action performed by a method.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $fqdn Fully qualified domain name.
	 * @return WP_Error|boolean       True on pass. WP_Error on failure.
	 */
	private function _basic_check( $fqdn = '' ) {
		if ( empty( $fqdn ) ) {
			return new WP_Error( 'empty', __( 'Please include a fully qualified domain name to be added.', 'dark-matter' ) );
		}

		/**
		 * Ensure that the URL is purely a domain. In order for the parse_url() to work, the domain must be prefixed
		 * with a double forward slash.
		 */
		if ( false === stripos( $fqdn, '//' ) ) {
            // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			$domain_parts = parse_url( '//' . ltrim( $fqdn, '/' ) );
		} else {
            // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			$domain_parts = parse_url( $fqdn );
		}

		if ( ! empty( $domain_parts['path'] ) || ! empty( $domain_parts['port'] ) || ! empty( $domain_parts['query'] ) ) {
			return new WP_Error( 'unsure', __( 'The domain provided contains path, port, or query string information. Please removed this before continuing.', 'dark-matter' ) );
		}

		$fqdn = $domain_parts['host'];

		/**
		 * Check to ensure we have a valid domain to work with.
		 */
		if ( ! filter_var( $fqdn, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME ) ) {
			return new WP_Error( 'domain', __( 'The domain is not valid.', 'dark-matter' ) );
		}

		if ( defined( 'DOMAIN_CURRENT_SITE' ) && DOMAIN_CURRENT_SITE === $fqdn ) {
			return new WP_Error( 'wp-config', __( 'You cannot configure the WordPress Network primary domain.', 'dark-matter' ) );
		}

		if ( is_main_site() ) {
			return new WP_Error( 'root', __( 'Domains cannot be mapped to the main / root Site.', 'dark-matter' ) );
		}

		$reserve = DarkMatter_Restrict::instance();
		if ( $reserve->is_exist( $fqdn ) ) {
			return new WP_Error( 'reserved', __( 'This domain has been reserved.', 'dark-matter' ) );
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
		 * @param string $fqdn Fully qualified domain name.
		 */
		return apply_filters( 'darkmatter_domain_basic_check', $fqdn );
	}

	/**
	 * Clears out the relevant caches, usually when a domain is added / updated / deleted.
	 *
	 * @since 2.2.0
	 *
	 * @param string $fqdn Fully qualified domain name. Optional.
	 */
	private function _clear_caches( $fqdn = '' ) {
		$this->clear_cache_for_domain( $fqdn );
		$this->clear_cache_for_domain_type();
		$this->clear_cache_for_domain_type( DM_DOMAIN_TYPE_MEDIA );
	}

	/**
	 * Add a domain for a specific Site in WordPress.
	 *
	 * @since 2.0.0
	 *
	 * @param  string  $fqdn       Domain to be updated.
	 * @param  boolean $is_primary Primary domain setting.
	 * @param  boolean $is_https   HTTPS protocol setting.
	 * @param  boolean $force      Whether the update should be forced.
	 * @param  boolean $active     Default is active. Set to false if you wish to add a domain but not make it active.
	 * @param  integer $type       Domain type. Defaults to `1`, which is "main".
	 * @return DM_Domain|WP_Error             DM_Domain on success. WP_Error on failure.
	 */
	public function add( $fqdn = '', $is_primary = false, $is_https = false, $force = true, $active = true, $type = DM_DOMAIN_TYPE_MAIN ) {
		$fqdn = $this->_basic_check( $fqdn );

		if ( is_wp_error( $fqdn ) ) {
			return $fqdn;
		}

		/**
		 * Check that the FQDN is not already stored in the database.
		 */
		if ( $this->is_exist( $fqdn ) ) {
			return new WP_Error( 'exists', __( 'This domain is already assigned to a Site.', 'dark-matter' ) );
		}

		$dm_primary = DarkMatter_Primary::instance();

		if ( $is_primary ) {
			$primary_domain = $dm_primary->get();

			/**
			 * Check to make sure another domain isn't set to Primary (can be overridden by the --force flag).
			 */
			if ( ! empty( $primary_domain ) ) {
				if ( ! $force ) {
					return new WP_Error( 'primary', __( 'You cannot add this domain as the primary domain without using the force flag.', 'dark-matter' ) );
				}
			}
		}

		/**
		 * Check the type is valid.
		 */
		if ( DM_DOMAIN_TYPE_MAIN !== $type && DM_DOMAIN_TYPE_MEDIA !== $type ) {
			return new WP_Error( 'type', __( 'The type for the new domain is not supported.', 'dark-matter' ) );
		}

		$_domain = array(
			'active'     => ( ! $active ? false : true ),
			'blog_id'    => get_current_blog_id(),
			'domain'     => $fqdn,
			'is_primary' => ( ! $is_primary ? false : true ),
			'is_https'   => ( ! $is_https ? false : true ),
			'type'       => ( ! empty( $type ) ? $type : DM_DOMAIN_TYPE_MAIN ),
		);

		$result = $this->wpdb->insert(
			$this->dm_table,
			$_domain,
			array(
				'%d',
				'%d',
				'%s',
				'%d',
				'%d',
				'%d',
			)
		);

		if ( $result ) {
			/**
			 * Clear internal cache.
			 */
			$this->_clear_caches();

			/**
			 * Create the cache key.
			 */
			$cache_key = md5( $fqdn );

			/**
			 * Update the domain object prior to priming the cache for both the
			 * domain object and the primary domain if necessary.
			 */
			$_domain['id'] = $this->wpdb->insert_id;
			wp_cache_add( $cache_key, $_domain, 'dark-matter' );

			if ( $is_primary ) {
				$this->primary_set( $fqdn, get_current_blog_id() );
			}

			$dm_domain = new DM_Domain( (object) $_domain );

			$this->update_last_changed();

			/**
			 * Fire action when a domain is added.
			 *
			 * Fires after a domain is successfully added to the database. This
			 * is also post insertion to the cache.
			 *
			 * @since 2.0.0
			 *
			 * @param DM_Domain $dm_domain Domain object of the newly added Domain.
			 */
			do_action( 'darkmatter_domain_add', $dm_domain );

			return $dm_domain;
		}

		return new WP_Error( 'unknown', __( 'Sorry, the domain could not be added. An unknown error occurred.', 'dark-matter' ) );
	}

	/**
	 * Clears the cache entry for a specific domain.
	 *
	 * @param string $fdqn Fully qualified domain name.
	 * @return bool True if the cache is deleted. False otherwise.
	 *
	 * @since 2.2.0
	 */
	public function clear_cache_for_domain( $fdqn = '' ) {
		if ( ! empty( $fqdn ) ) {
			$cache_key = md5( $fqdn );
			$success   = wp_cache_delete( $cache_key, 'dark-matter' );

			if ( $success ) {
				$this->update_last_changed();
			}

			return $success;
		}

		return false;
	}

	/**
	 * Clear the cache for a specific domain type, "main" or "media".
	 *
	 * @param integer $type    Type of domain, 1 = "main" and 2 = "media".
	 * @param integer $site_id Site for which the cache will be updated. Defaults to the current site if not specified.
	 * @return bool True if cache is deleted. False otherwise.
	 *
	 * @since 2.2.0
	 */
	public function clear_cache_for_domain_type( $type = DM_DOMAIN_TYPE_MAIN, $site_id = 0 ) {
		/**
		 * Use the current site ID if no site is provided.
		 */
		if ( empty( $site_id ) ) {
			$site_id = get_current_blog_id();
		}

		/**
		 * Delete main type domains.
		 */
		$cache_key = sprintf(
			'%1$d-%2$d-domain-types',
			$type,
			$site_id
		);

		$success = wp_cache_delete( md5( $cache_key ), 'dark-matter' );

		if ( $success ) {
			$this->update_last_changed();
		}

		return $success;
	}

	/**
	 * Delete a domain for a specific Site in WordPress.
	 *
	 * @since 2.0.0
	 *
	 * @param  string  $fqdn FQDN to be deleted.
	 * @param  boolean $force Force the FQDN to be deleted, even if it is the primary domain.
	 * @return WP_Error|boolean True on success. False otherwise.
	 */
	public function delete( $fqdn = '', $force = true ) {
		$fqdn = $this->_basic_check( $fqdn );

		if ( is_wp_error( $fqdn ) ) {
			return $fqdn;
		}

		/**
		 * Cannot delete what does not exist.
		 */
		if ( ! $this->is_exist( $fqdn ) ) {
			return new WP_Error( 'exists', __( 'The domain cannot be found.', 'dark-matter' ) );
		}

		/**
		 * Check to make sure the domain is assigned to the site.
		 */
		$_domain = $this->get( $fqdn );

		if ( ! $_domain || get_current_blog_id() !== $_domain->blog_id ) {
			return new WP_Error( 'not found', __( 'The domain cannot be found.', 'dark-matter' ) );
		}

		/**
		 * Check to make sure that the domain is not a primary and if it is that
		 * the force flag has been provided.
		 */
		if ( $_domain->is_primary ) {
			if ( $force ) {
				$this->primary_unset( $_domain->domain, $_domain->blog_id );
			} else {
				return new WP_Error( 'primary', __( 'This domain is the primary domain for this Site. Please provide the force flag to delete.', 'dark-matter' ) );
			}
		}

		$result = $this->wpdb->delete(
			$this->dm_table,
			array(
				'domain' => $fqdn,
			),
			array( '%s' )
		);

		if ( $result ) {
			/**
			 * Clear the caches, including the domain.
			 */
			$this->_clear_caches( $fqdn );

			/**
			 * Fire action when a domain is deleted.
			 *
			 * Fires after a domain is successfully deleted to the database.
			 * This is also after the domain is deleted from cache.
			 *
			 * @since 2.0.0
			 *
			 * @param DM_Domain $_domain Domain object that was deleted.
			 */
			do_action( 'darkmatter_domain_delete', $_domain );

			return true;
		}

		return new WP_Error( 'unknown', __( 'Sorry, the domain could not be deleted. An unknown error occurred.', 'dark-matter' ) );

	}

	/**
	 * Find a domain for a specific Site in WordPress.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $fqdn FQDN to search for.
	 * @return DM_Domain|boolean       Domain object. False on failure or not found.
	 */
	public function find( $fqdn = '' ) {
		if ( empty( $fqdn ) ) {
			return null;
		}

		return $this->get( $fqdn );
	}

	/**
	 * Retrieve a domain for a specific Site in WordPress.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $fqdn FQDN to search for.
	 * @return DM_Domain|boolean       Domain object. False otherwise.
	 */
	public function get( $fqdn = '' ) {
		if ( empty( $fqdn ) ) {
			return false;
		}

		/**
		 * If the domain provided is the DOMAIN_CURRENT_SITE / network domain,
		 * then there is no point doing a database look up as it is clearly not
		 * a mapped URL.
		 */
		if ( defined( 'DOMAIN_CURRENT_SITE' ) && DOMAIN_CURRENT_SITE === $fqdn ) {
			return false;
		}

		/**
		 * Attempt to retrieve the domain from cache.
		 */
		$cache_key = md5( $fqdn );

		/**
		 * Make sure if the cache returns a value, that it's set to an object rather than an array.
		 */
		$_domain = wp_cache_get( $cache_key, 'dark-matter' );
		if ( is_array( $_domain ) ) {
			$_domain = (object) $_domain;
		}

		/**
		 * If the domain cannot be retrieved from cache, attempt to retrieve it
		 * from the database.
		 */
		if ( ! $_domain ) {
            // phpcs:ignore
            $_domain = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM {$this->dm_table} WHERE domain = %s", $fqdn ) );

			if ( empty( $_domain ) ) {
				return false;
			}

			/**
			 * Update the cache.
			 */
			wp_cache_add( $cache_key, $_domain, 'dark-matter' );

			/**
			 * We update the last changed here as the cache was modified.
			 */
			$this->update_last_changed();
		}

		/**
		 * When the domain is retrieved _and_ the domain is a "primary domain", then double-check that the primary
		 * domain cache is set for it as well.
		 */
		$primary_cache_key = $_domain->blog_id . '-primary';
		if ( $_domain->is_primary && empty( wp_cache_get( $primary_cache_key, 'dark-matter' ) ) ) {
			wp_cache_set( $primary_cache_key, $_domain->domain, 'dark-matter' );
		}

		/**
		 * Return the DM_Domain object version.
		 */
		return new DM_Domain( (object) $_domain );
	}

	/**
	 * Retrieve a List of domains by type. For example: retrieving a list of Media domains.
	 *
	 * @since 2.2.0
	 *
	 * @param  integer $type       Domain type to retrieve.
	 * @param  integer $site_id    Site ID to retrieve mapped domains for.
	 * @param  boolean $skip_cache Skip the cache and retrieve the Media domains from the database.
	 * @return array               An array of DM_Domain objects. Returns an empty array if no mapped domains found or on error.
	 */
	public function get_domains_by_type( $type = DM_DOMAIN_TYPE_MEDIA, $site_id = 0, $skip_cache = false ) {
		global $wpdb;

		/**
		 * Validate type before continuing.
		 */
		if ( DM_DOMAIN_TYPE_MAIN !== $type && DM_DOMAIN_TYPE_MEDIA !== $type ) {
			return [];
		}

		/**
		 * Media domains can be set with a constant.
		 */
		if ( DM_DOMAIN_TYPE_MEDIA === $type && ! empty( $this->network_media ) ) {
			/**
			 * Ensure it is an array and then return. No checks, we assume that is provided correctly.
			 */
			if ( is_array( $this->network_media ) ) {
				$media_domains = [];

				/**
				 * Convert the domains into DM_Domain objects.
				 */
				foreach ( $this->network_media as $i => $media_domain ) {
					$media_domains[ $i ] = new DM_Domain(
						(object) [
							'active'     => true,
							'blog_id'    => get_current_blog_id(),
							'domain'     => $media_domain,
							'id'         => -1,
							'is_https'   => true,
							'is_primary' => false,
							'type'       => DM_DOMAIN_TYPE_MEDIA,
						]
					);
				}

				return $media_domains;
			}
		}

		/**
		 * Setup the cache key.
		 */
		$site_id   = ( empty( $site_id ) ? get_current_blog_id() : $site_id );
		$cache_key = md5(
			sprintf(
				'%1$d-%2$d-domain-types',
				$type,
				$site_id
			)
		);

		/**
		 * Retrieve the cache and if some thing is available.
		 */
		$_domains = wp_cache_get( $cache_key, 'dark-matter' );

		if ( $skip_cache || ! is_array( $_domains ) ) {
			$_domains = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT domain FROM {$this->dm_table} WHERE blog_id = %d AND type = %d AND active = 1 ORDER BY domain", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$site_id,
					$type
				)
			);

			/**
			 * Update the cache with the records from the database.
			 */
			wp_cache_set( $cache_key, $_domains, 'dark-matter' );

			/**
			 * As the cache is updated here.
			 */
			$this->update_last_changed();
		}

		if ( empty( $_domains ) ) {
			return [];
		}

		/**
		 * Retrieve the domain details, probably from cache, and get an array of `DM_Domain` objects.
		 */
		$domains = array();

		foreach ( $_domains as $_domain ) {
			$domains[] = $this->get( $_domain );
		}

		return $domains;
	}

	/**
	 * Retrieve a List of domains. If the Site ID is empty or this is called
	 * from the root Site, then this will return all domains mapped on the
	 * Network.
	 *
	 * @since 2.0.0
	 *
	 * @param  integer $site_id Site ID to retrieve mapped domains for.
	 * @return array            An array of DM_Domain objects. Returns an empty array if no mapped domains found or on error.
	 */
	public function get_domains( $site_id = 0 ) {
		global $wpdb;

		$_domains = null;

		if ( ! empty( $site_id ) ) {
            // phpcs:ignore
            $_domains = $wpdb->get_col( $wpdb->prepare( "SELECT domain FROM {$this->dm_table} WHERE blog_id = %d ORDER BY is_primary DESC, domain", $site_id ) );
		} else {
            // phpcs:ignore
            $_domains = $wpdb->get_col( "SELECT domain FROM {$this->dm_table} ORDER BY blog_id, is_primary DESC, domain" );
		}

		if ( empty( $_domains ) ) {
			return [];
		}

		/**
		 * Retrieve the domain details from the cache. If the cache is
		 */
		$domains = array();

		foreach ( $_domains as $_domain ) {
			$domains[] = $this->get( $_domain );
		}

		return $domains;
	}

	/**
	 * Check if a domain exists. This checks against all websites and is not
	 * site specific.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $fqdn FQDN to search for.
	 * @return boolean       True if found. False otherwise.
	 */
	public function is_exist( $fqdn = '' ) {
		if ( empty( $fqdn ) ) {
			return false;
		}

        // phpcs:ignore
        $_domain = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT id FROM {$this->dm_table} WHERE domain = %s LIMIT 1", $fqdn ) );

		return ( null !== $_domain );
	}

	/**
	 * Check if a domain is reserved. This checks against all websites and is
	 * not site specific.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $fqdn FQDN to search for.
	 * @return boolean       True if the domain is reserved. False otherwise.
	 */
	public function is_reserved( $fqdn = '' ) {
		if ( empty( $fqdn ) ) {
			return false;
		}

		return false;
	}

	/**
	 * Helper to set the Primary Domain cache.
	 *
	 * @since 2.3.0
	 *
	 * @param string  $domain  Domain to set as primary.
	 * @param integer $blog_id Blog ID for the site to have the new primary.
	 * @return void
	 */
	private function primary_set( $domain = '', $blog_id = 0 ) {
		$current_primary = DarkMatter_Primary::instance()->get( $blog_id );

		if ( ! empty( $current_primary ) && $domain !== $current_primary->domain ) {
			$this->update( $current_primary->domain, false, null, true, $current_primary->active );
		}

		/**
		 * Update the primary domain cache.
		 */
		$primary_cache_key = $blog_id . '-primary';

		/**
		 * Fires when a domain is set to be the primary for a Site.
		 *
		 * @since 2.0.0
		 *
		 * @param  string  $domain     Domain that is unset to primary domain.
		 * @param  integer $site_id    Site ID.
		 * @param  boolean $deprecated Redundant, is always true as database is now always updated.
		 */
		do_action( 'darkmatter_primary_set', $domain, $blog_id, true );

		wp_cache_set( $primary_cache_key, $domain, 'dark-matter' );
	}

	/**
	 * Helper to set the Primary Domain cache.
	 *
	 * @since 2.3.0
	 *
	 * @param string  $domain  Domain to unset as primary.
	 * @param integer $blog_id Blog ID for the site to remove the primary.
	 * @return void
	 */
	private function primary_unset( $domain = '', $blog_id = 0 ) {
		$primary_cache_key = $blog_id . '-primary';
		wp_cache_delete( $primary_cache_key, 'dark-matter' );

		/**
		 * Fires when a domain is unset to be the primary for a Site.
		 *
		 * @since 2.0.0
		 *
		 * @param  string  $domain     Domain that is unset to primary domain.
		 * @param  integer $site_id    Site ID.
		 * @param  boolean $deprecated Redundant, is always true as database is now always updated.
		 */
		do_action( 'darkmatter_primary_unset', $domain, $blog_id, true );
	}

	/**
	 * Add a reserved domain for the Network in WordPress.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $fqdn FQDN to be added.
	 * @return boolean       True on success. False otherwise.
	 */
	public function reserve( $fqdn = '' ) {
		if ( empty( $fqdn ) ) {
			return false;
		}

		return false;
	}

	/**
	 * Find a domain for a specific Site in WordPress.
	 *
	 * @since 2.0.0
	 *
	 * @param  string  $fqdn       Domain to be updated.
	 * @param  boolean $is_primary Primary domain setting.
	 * @param  boolean $is_https   HTTPS protocol setting.
	 * @param  boolean $force      Whether the update should be forced.
	 * @param  boolean $active     Default is active. Set to false if you wish to add a domain but not make it active.
	 * @param  integer $type       Domain type. Defaults to `null`, do not change current value. Accepts `1` for main and `2` for Media.
	 * @return DM_Domain|WP_Error             DM_Domain on success. WP_Error on failure.
	 */
	public function update( $fqdn = '', $is_primary = null, $is_https = null, $force = true, $active = true, $type = null ) {
		$fqdn = $this->_basic_check( $fqdn );

		if ( is_wp_error( $fqdn ) ) {
			return $fqdn;
		}

		$domain_before = $this->get( $fqdn );

		if ( ! $domain_before ) {
			return new WP_Error( 'not found', __( 'Cannot find the domain to update.', 'dark-matter' ) );
		}

		$dm_primary = DarkMatter_Primary::instance();

		$_domain = array(
			'active'  => ( ! $active ? false : true ),
			'blog_id' => $domain_before->blog_id,
			'domain'  => $fqdn,
		);

		/**
		 * Determine if there is an attempt to update the "is primary" field.
		 */
		if ( null !== $is_primary && $is_primary !== $domain_before->is_primary ) {
			/**
			 * Any update to the "is primary" requires the force flag.
			 */
			if ( ! $force ) {
				return new WP_Error( 'primary', __( 'You cannot update the primary flag without setting the force parameter to true', 'dark-matter' ) );
			}

			$_domain['is_primary'] = $is_primary;
		}

		if ( null !== $is_https ) {
			$_domain['is_https'] = $is_https;
		}

		/**
		 * Type is either "main" or "media".
		 */
		if ( null !== $type ) {
			if ( DM_DOMAIN_TYPE_MAIN === $type || DM_DOMAIN_TYPE_MEDIA === $type ) {
				$_domain['type'] = $type;
			} else {
				return new WP_Error( 'type', __( 'The type for the new domain is not supported.', 'dark-matter' ) );
			}
		}

		$result = $this->wpdb->update(
			$this->dm_table,
			$_domain,
			array(
				'id' => $domain_before->id,
			)
		);

		if ( $result ) {
			/**
			 * Stitch together the current domain record with the updates for the
			 * cache.
			 */
			$_domain = wp_parse_args( $_domain, $domain_before->to_array() );

			/**
			 * Clear the caches, but not the domain.
			 */
			$this->_clear_caches();

			/**
			 * Create the cache key.
			 */
			$cache_key = md5( $fqdn );

			/**
			 * Update the domain object prior to updating the cache for both the
			 * domain object and the primary domain if necessary.
			 */
			$_domain['id'] = $domain_before->id;
			wp_cache_set( $cache_key, $_domain, 'dark-matter' );

			/**
			 * Handle changes to the primary setting if required.
			 */
			if ( $is_primary && ! $domain_before->is_primary ) {
				$this->primary_set( $domain_before->domain, $domain_before->blog_id );
			} elseif ( false === $is_primary && $domain_before->is_primary ) {
				$this->primary_unset( $domain_before->domain, $domain_before->blog_id );
			}

			$domain_after = new DM_Domain( (object) $_domain );

			$this->update_last_changed();

			/**
			 * Fires when a domain is updated.
			 *
			 * @since 2.0.0
			 *
			 * @param DM_Domain $domain_after  Domain object after the changes have been applied successfully.
			 * @param DM_Domain $domain_before Domain object before.
			 */
			do_action( 'darkmatter_domain_updated', $domain_after, $domain_before );

			return $domain_after;
		}

		return new WP_Error( 'unknown', __( 'Sorry, the domain could not be updated. An unknown error occurred.', 'dark-matter' ) );
	}

	/**
	 * Change the last changed cache note.
	 *
	 * @since 2.1.8
	 *
	 * @return void
	 */
	private function update_last_changed() {
		wp_cache_set( 'last_changed', microtime(), 'dark-matter' );
	}

	/**
	 * Return the Singleton Instance of the class.
	 *
	 * @since 2.0.0
	 *
	 * @return DarkMatter_Domains
	 */
	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}

<?php
/**
 * Class DarkMatter_Primary
 *
 * @package DarkMatter
 */

defined( 'ABSPATH' ) || die;

// phpcs:disable PHPCompatibility.Keywords.ForbiddenNames.unsetFound

/**
 * Class DarkMatter_Primary
 */
class DarkMatter_Primary {
	/**
	 * The Domain Mapping table name for use by the various methods.
	 *
	 * @var string
	 */
	private $dmtable = '';

	/**
	 * Reference to the global $wpdb and is more for code cleaniness.
	 *
	 * @var boolean
	 */
	private $wpdb = false;

	/**
	 * Constructor
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
	}

	/**
	 * Retrieve the Primary domain for a Site.
	 *
	 * @param  integer $site_id Site ID to retrieve the primary domain for.
	 * @return DM_Domain|boolean          Returns the DM_Domain object on success. False otherwise.
	 */
	public function get( $site_id = 0 ) {
		$site_id = ( empty( $site_id ) ? get_current_blog_id() : $site_id );

		/**
		 * Attempt to retrieve the domain from cache.
		 */
		$cache_key      = $site_id . '-primary';
		$primary_domain = wp_cache_get( $cache_key, 'dark-matter' );

		/**
		 * If the Cache is unavailable, then attempt to load the domain from the
		 * database and re-prime the cache.
		 */
		if ( ! $primary_domain ) {
            // phpcs:ignore
            $primary_domain = $this->wpdb->get_var( $this->wpdb->prepare( "SELECT domain FROM {$this->dm_table} WHERE is_primary = 1 AND blog_id = %s", $site_id ) );

			if ( empty( $primary_domain ) ) {
				/**
				 * Set the cached value for Primary Domain to "none". This will
				 * stop spurious database queries for some thing that has not
				 * been setup up.
				 */
				wp_cache_set( $cache_key, 'none', 'dark-matter' );
				return false;
			}

			$this->set( $site_id, $primary_domain );
		}

		/**
		 * Return false if the cache value is "none".
		 */
		if ( 'none' === $primary_domain ) {
			return false;
		}

		/**
		 * Retrieve the entire Domain object.
		 */
		$db      = DarkMatter_Domains::instance();
		$_domain = $db->get( $primary_domain );

		return $_domain;
	}

	/**
	 * Retrieve all primary domains for the Network.
	 *
	 * @return array Array of DM_Domain objects of the Primary domains for each Site in the Network.
	 */
	public function get_all() {
		global $wpdb;

        // phpcs:ignore
        $_domains = $wpdb->get_col( "SELECT domain FROM {$this->dm_table} WHERE is_primary = 1 ORDER BY blog_id DESC, domain" );

		if ( empty( $_domains ) ) {
			return array();
		}

		$db = DarkMatter_Domains::instance();

		/**
		 * Retrieve the DM_Domain objects for each of the primary domains.
		 */
		$domains = array();

		foreach ( $_domains as $_domain ) {
			$domains[] = $db->get( $_domain );
		}

		return $domains;
	}

	/**
	 * Helper function to the set the cache for the primary domain for a Site.
	 *
	 * @param  integer $site_id Site ID to set the primary domain cache for.
	 * @param  string  $domain  Domain to be stored in the cache.
	 * @param  boolean $db      Set to true to perform a database update.
	 * @return void
	 */
	public function set( $site_id = 0, $domain = '', $db = false ) {
		$site_id   = ( empty( $site_id ) ? get_current_blog_id() : $site_id );
		$cache_key = $site_id . '-primary';

		if ( $db ) {
			$result = $this->wpdb->update(
				$this->dm_table,
				array(
					'is_primary' => true,
				),
				array(
					'domain' => $domain,
				)
			);
		}

		/**
		 * Fires when a domain is set to be the primary for a Site.
		 *
		 * @since 2.0.0
		 *
		 * @param  string  $domain  Domain that is set to primary domain.
		 * @param  integer $site_id Site ID.
		 * @param  boolean $db      States if the change performed a database update.
		 */
		do_action( 'darkmatter_primary_set', $domain, $site_id, $db );

		wp_cache_set( $cache_key, $domain, 'dark-matter' );
	}

	/**
	 * Unset the primary domain for a given Site. By default, will change all
	 * records with is_primary set to true.
	 *
	 * @param  integer $site_id Site ID to unset the primary domain for.
	 * @param  string  $domain  Optional. If provided, will only affect that domain's record.
	 * @param  boolean $db      Set to true to perform a database update.
	 * @return boolean          True on success. False otherwise.
	 */
	public function unset( $site_id = 0, $domain = '', $db = false ) {
		$site_id = ( empty( $site_id ) ? get_current_blog_id() : $site_id );

		if ( $db ) {
			/**
			 * Construct the where clause.
			 */
			$where = array(
				'blog_id' => $site_id,
			);

			if ( ! empty( $domain ) ) {
				$where['domain'] = $domain;
			}

			$result = $this->wpdb->update(
				$this->dm_table,
				array(
					'is_primary' => false,
				),
				$where,
				array(
					'%d',
				)
			);

			if ( false === $result ) {
				return false;
			}
		}

		/**
		 * Fires when a domain is unset to be the primary for a Site.
		 *
		 * @since 2.0.0
		 *
		 * @param  string  $domain  Domain that is unset to primary domain.
		 * @param  integer $site_id Site ID.
		 * @param  boolean $db      States if the change performed a database update.
		 */
		do_action( 'darkmatter_primary_unset', $domain, $site_id, $db );

		$cache_key = $site_id . '-primary';
		wp_cache_delete( $cache_key, 'dark-matter' );

		return true;
	}

	/**
	 * Return the Singleton Instance of the class.
	 *
	 * @return DarkMatter_Primary
	 */
	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}

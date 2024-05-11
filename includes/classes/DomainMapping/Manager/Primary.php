<?php
/**
 * Handles the management of Primary domains.
 *
 * @since 2.0.0
 *
 * @package DarkMatterPlugin\DomainMapping
 */

namespace DarkMatter\DomainMapping\Manager;

// phpcs:disable PHPCompatibility.Keywords.ForbiddenNames.unsetFound

use \DarkMatter\DomainMapping\Data;

/**
 * Class Primary
 *
 * Previously called `DarkMatter_Primary`.
 *
 * @since 2.0.0
 */
class Primary {
	/**
	 * The Domain Mapping table name for use by the various methods.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	private $dmtable = '';

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
	}

	/**
	 * Retrieve the Primary domain for a Site.
	 *
	 * @since 2.0.0
	 *
	 * @param int $site_id Site ID to retrieve the primary domain for.
	 * @return Data\Domain|boolean Returns the DM_Domain object on success. False otherwise.
	 */
	public function get( $site_id = 0 ) {
		$site_id = ( empty( $site_id ) ? get_current_blog_id() : $site_id );

		$query = new Data\DomainQuery(
			[
				'blog_id'    => $site_id,
				'is_primary' => true,
				'number'     => 1,
			]
		);

		if ( empty( $query->records ) ) {
			return false;
		}

		return $query->records[0];
	}

	/**
	 * Retrieve all primary domains for the Network.
	 *
	 * @since 2.0.0
	 *
	 * @return Data\Domain[] of Domain objects of the Primary domains for each Site in the Network.
	 */
	public function get_all( $count = 10, $page = 1 ) {
		$query = new Data\DomainQuery(
			[
				'number'     => $count,
				'page'       => $page,
				'is_primary' => true,
			]
		);

		return $query->records;
	}

	/**
	 * Helper function to the set the cache for the primary domain for a Site.
	 *
	 * @since 2.0.0
	 *
	 * @param  integer $site_id Site ID to set the primary domain cache for.
	 * @param  string  $domain  Domain to be stored in the cache.
	 * @return boolean True on success, false otherwise.
	 */
	public function set( $site_id = 0, $domain = '' ) {
		$new_primary_domain = Domain::instance()->get( $domain );

		if ( $new_primary_domain->blog_id !== $site_id ) {
			return false;
		}

		$result = Domain::instance()->update(
			$new_primary_domain->domain,
			true,
			$new_primary_domain->is_https,
			true,
			$new_primary_domain->active,
			DM_DOMAIN_TYPE_MAIN
		);

		if ( is_wp_error( $result ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Unset the primary domain for a given Site. By default, will change all
	 * records with is_primary set to true.
	 *
	 * @since 2.0.0
	 *
	 * @param  integer $site_id Site ID to unset the primary domain for.
	 * @param  string  $domain  Optional. If provided, will only affect that domain's record.
	 * @param  boolean $db      Set to true to perform a database update.
	 * @return boolean          True on success. False otherwise.
	 */
	public function unset( $site_id = 0, $domain = '', $db = false ) {
		$new_primary_domain = Domain::instance()->get( $domain );

		if ( $new_primary_domain->blog_id !== $site_id ) {
			return false;
		}

		$result = Domain::instance()->update(
			$new_primary_domain->domain,
			false,
			$new_primary_domain->is_https,
			true,
			$new_primary_domain->active,
			DM_DOMAIN_TYPE_MAIN
		);

		if ( is_wp_error( $result ) ) {
			return false;
		}

		return true;
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
	 * @return Primary
	 */
	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}

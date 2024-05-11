<?php
/**
 * Handles any installation logic needed for the Domain Mapping module.
 *
 * @since 2.0.0
 *
 * @package DarkMatterPlugin\DomainMapping
 */

namespace DarkMatter\DomainMapping;

use DarkMatter\DomainMapping\Data\DomainMapping;
use DarkMatter\DomainMapping\Data\RestrictedDomain;

/**
 * Class Installer
 *
 * Previously called `DM_Database`.
 *
 * @since 2.0.0
 */
class Installer {
	/**
	 * Class for handling the custom table for Domain Mapping.
	 *
	 * @var DomainMapping
	 */
	public static $domain_table;

	/**
	 * Class for handling the custom table for Restricted Domains.
	 *
	 * @var RestrictedDomain
	 */
	public static $restricted_domain;

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		self::prepare_tables();

		add_action( 'init', [ $this, 'maybe_upgrade' ] );
	}

	/**
	 * Check to see if the database upgrade is required. If so, then perform the
	 * necessary table creation / update commands.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function maybe_upgrade() {
		if ( update_network_option( null, 'dark_matter_db_version', DM_DB_VERSION ) ) {
			/**
			 * As dbDelta function is called, ensure that this part of the
			 * WordPress API is included.
			 */
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			self::$domain_table->create_update_table();
			self::$restricted_domain->create_update_table();
		}
	}

	/**
	 * Prepare the table classes for use.
	 *
	 * @return void
	 */
	public static function prepare_tables() {
		self::$domain_table      = new DomainMapping();
		self::$restricted_domain = new RestrictedDomain();
	}
}

<?php
/**
 * Defines the Domain Mapping module of the Dark Matter Plugin.
 *
 * @package DarkMatter\DomainMapping
 */

namespace DarkMatter\DomainMapping;

use DarkMatter\DomainMapping\Data\DomainMapping;
use DarkMatter\DomainMapping\Data\RestrictedDomain;
use DarkMatter\Helper\Module;

/**
 * Class DomainMapping.
 */
class DomainMappingModule extends Module {

	/**
	 * The class instance.
	 *
	 * @var null|DomainMappingModule
	 */
	private static $instance = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( 'DomainMapping' );

		$this->load();
		$this->maybe_upgrade();
	}

	/**
	 * @return void
	 */
	public function maybe_upgrade() {
		if ( update_network_option( 0, 'dark_matter_db_version', DM_DB_VERSION ) ) {
			/**
			 * As dbDelta function is called, ensure that this part of the
			 * WordPress API is included.
			 */
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$domain_table = new DomainMapping();
			$domain_table->create_update_table();

			$restricted_table = new RestrictedDomain();
			$restricted_table->create_update_table();
		}
	}

	/**
	 * Singleton.
	 *
	 * @return DomainMappingModule
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

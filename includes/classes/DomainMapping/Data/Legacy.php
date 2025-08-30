<?php
/**
 * Handle the database definitions for Domain Mapping in the same manner as the original DM_Database.
 *
 * @package DarkMatterPlugin\DomainMapping
 */

namespace DarkMatterPlugin\DomainMapping\Data;

use DarkMatterPlugin\Registerable;

/**
 * Class Legacy.
 */
class Legacy implements Registerable {

	/**
	 * Database version.
	 *
	 * @var string
	 */
	private $version = '20210517';

	/**
	 * Can the Legacy can be used.
	 *
	 * @return true
	 */
	public function can_register() {
		return true;
	}

	/**
	 * Run the database upgrade if needed.
	 *
	 * @return void
	 */
	public function maybe_upgrade() {
		if ( update_network_option( null, 'dark_matter_db_version', $this->version ) ) {
			/**
			 * As dbDelta function is called, ensure that this part of the
			 * WordPress API is included.
			 */
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$this->upgrade_domains();
			$this->upgrade_restrict();
		}
	}

	/**
	 * Handle actions and filters for Data Legacy.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', [ $this, 'maybe_upgrade' ] );
	}

	/**
	 * Upgrade the domains table.
	 *
	 * @return void
	 */
	public function upgrade_domains() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE `{$wpdb->base_prefix}domain_mapping` (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            blog_id BIGINT(20) NOT NULL,
            is_primary TINYINT(4) DEFAULT '0',
            domain VARCHAR(255) NOT NULL,
            active TINYINT(4) DEFAULT '1',
            is_https TINYINT(4) DEFAULT '0',
            type TINYINT(4) DEFAULT '1',
            PRIMARY KEY  (id)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Upgrade the Reserve domains table.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function upgrade_restrict() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE `{$wpdb->base_prefix}domain_restrict` (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            domain VARCHAR(255) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

		dbDelta( $sql );
	}
}

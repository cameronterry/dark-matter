<?php
/**
 * Class DM_Database
 *
 * @package DarkMatter
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || die;

/**
 * Class DM_Database
 *
 * @since 2.0.0
 */
class DM_Database {
	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'maybe_upgrade' ) );
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

			$this->upgrade_domains();
			$this->upgrade_restrict();
		}
	}

	/**
	 * Upgrade the domains table.
	 *
	 * @since 2.0.0
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

	/**
	 * Return the Singleton Instance of the class.
	 *
	 * @since 2.0.0
	 *
	 * @return DM_Database
	 */
	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}
DM_Database::instance();

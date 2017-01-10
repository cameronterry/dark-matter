<?php
/**
 * Plugin Name: Dark Matter
 * Plugin URI: https://cameronterry.supernovawp.com/dark-matter/
 * Description: A domain mapping plugin from Project Dark Matter.
 * Version: 1.0.0 RC5
 * Author: Cameron Terry
 * Author URI: https://cameronterry.supernovawp.com/
 * Text Domain: dark-matter
 *
 * Dark Matter is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Dark Matter is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Dark Matter. If not, see
 * https://github.com/cameronterry/dark-matter/blob/master/license.txt.
 */

/** A bit of security for those who are too clever for their own good. */
defined( 'ABSPATH' ) or die();

/** Setup the Plugin Constants */
define( 'DM_PATH', plugin_dir_path( __FILE__ ) );
define( 'DM_VERSION', '1.0.0 RC5' );
define( 'DM_DB_VERSION', '20170109' );

/**
 * Dark Matter Prepare
 */
global $current_blog, $wpdb;

/** Set the property for the Domain Mapping table. */
$wpdb->dmtable = $wpdb->base_prefix . 'domain_mapping';

/** Set the primary domain for the Current Blog. */
$mapped_domain = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->dmtable} WHERE blog_id = %s AND is_primary = 1 LIMIT 0, 1", $current_blog->blog_id ) );

if ( false === empty( $mapped_domain ) ) {
	$current_blog->https = boolval( $mapped_domain->is_https );
	$current_blog->primary_domain = $mapped_domain->domain;
}

/** Check to see if the Original Domain is present and if not, set it. */
if ( false === property_exists( $current_blog, 'original_domain' ) ) {
	$current_blog->original_domain = $current_blog->domain . $current_blog->path;
}

/**
 * Dark Matter plugin
 */
require_once( DM_PATH . '/inc/api.php' );
require_once( DM_PATH . '/inc/redirects.php' );
require_once( DM_PATH . '/inc/urls.php' );

require_once( DM_PATH . '/ui/actions.php' );
require_once( DM_PATH . '/ui/blog.php' );
require_once( DM_PATH . '/ui/nag.php' );

require_once( DM_PATH . '/sso/index.php' );

/**
 * When Dark Matter is activated, it will attempt to copy the sunrise.php file
 * to the correct destination.
 */
function dark_matter_activate() {
	$destination = WP_CONTENT_DIR . '/sunrise.php';
	$source = DM_PATH . '/sunrise.php';

	if ( is_writable( WP_CONTENT_DIR ) && false === file_exists( $destination ) && is_readable( $source ) ) {
		@copy( $source, $destination );
	}
}
register_activation_hook( __FILE__, 'dark_matter_activate' );

function dark_matter_enqueue_scripts( $hook ) {
	if ( 'settings_page_dark_matter_blog_settings' === $hook ) {
		wp_enqueue_style( 'dark-matter-css', plugin_dir_url( __FILE__ ) . 'ui/css/blog.css', null, DM_VERSION );
	}
}
add_action( 'admin_enqueue_scripts', 'dark_matter_enqueue_scripts' );

function dark_matter_maybe_create_tables() {
	global $wpdb;

	/** As dbDelta function is called before the upgrade file is included. */
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE `{$wpdb->dmtable}` (
		id BIGINT(20) NOT NULL AUTO_INCREMENT,
		blog_id BIGINT(20) NOT NULL,
		is_primary TINYINT(4) DEFAULT '0',
		domain VARCHAR(255) NOT NULL,
		active TINYINT(4) DEFAULT '1',
		is_https TINYINT(4) DEFAULT '0',
		PRIMARY KEY  (id)
	) $charset_collate;";

	dbDelta( $sql );
}

function dark_matter_maybe_upgrade() {
	if ( is_network_admin() ) {
		if ( update_network_option( null, 'dark_matter_db_version', DM_DB_VERSION ) ) {
			dark_matter_maybe_create_tables();
		}
	}
}

function dark_matter_plugins_loaded() {
	dark_matter_maybe_upgrade();
}
add_action( 'plugins_loaded', 'dark_matter_plugins_loaded' );

<?php
/**
 * Plugin Name: Dark Matter
 * Plugin URI: http://projectdarkmatter.com/dark-matter/
 * Description: A domain mapping plugin from Project Dark Matter.
 * Version: 0.0.0
 * Author: Cameron Terry
 * Author URI: https://cameronterry.supernovawp.com/
 */

/** A bit of security for those who are too clever for their own good. */
defined( 'ABSPATH' ) or die();

/** Setup the Plugin Constants */
define( 'DM_PATH', plugin_dir_path( __FILE__ ) );
define( 'DM_VERSION', '0.0.0' );

function dark_matter_maybe_create_tables() {
  global $wpdb;

  $charset_collate = $wpdb->get_charset_collate();

  $wpdb->dmtable = $wpdb->base_prefix . 'domain_mapping';

  $sql = "CREATE TABLE `{$wpdb->dmtable}` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `blog_id` BIGINT(20) NOT NULL,
    `domain` VARCHAR(255) NOT NULL,
    `active` TINYINT(4) DEFAULT '1',
    PRIMARY KEY  (`id`),
    KEY `blog_id` (`blog_id`,`domain`,`active`)
  ) $charset_collate;";

  dbDelta( $sql );
}

function dark_matter_maybe_upgrade() {
  if ( is_network_admin() ) {
    $current_version = get_network_option( null, 'dark_matter_version', null );

    if ( null == $current_version || version_compare( DM_VERSION, $current_version, '=' ) ) {
      dark_matter_maybe_create_tables();
      update_network_option( null, 'dark_matter_version', DM_VERSION );
    }
  }
}
add_action( 'plugins_loaded', 'dark_matter_maybe_upgrade' );

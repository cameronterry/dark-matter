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

require_once( DM_PATH . '/inc/ajax.php' );
require_once( DM_PATH . '/inc/api.php' );
require_once( DM_PATH . '/inc/redirects.php' );
require_once( DM_PATH . '/inc/urls.php' );

require_once( DM_PATH . '/ui/blog.php' );
require_once( DM_PATH . '/ui/network.php' );

function dark_matter_enqueue_scripts( $hook ) {
  if ( 'settings_page_dark_matter_blog_settings' === $hook ) {
    wp_enqueue_script( 'dark-matter-js', plugin_dir_url( __FILE__ ) . 'ui/js/dark-matter.js', array( 'jquery', 'wp-util' ), DM_VERSION );
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
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `blog_id` BIGINT(20) NOT NULL,
    `domain` VARCHAR(255) NOT NULL,
    `active` TINYINT(4) DEFAULT '1',
    PRIMARY KEY  (`id`),
    KEY `blog_id` (`blog_id`,`domain`,`active`)
  ) $charset_collate;";

  dbDelta( $sql );
}

function test( $value ) {
  echo( '<pre>' );
  print_r( $value );
  echo( '</pre>' );
  return $value;
}

// add_filter( 'pre_option_siteurl', 'test' );
// add_filter( 'pre_option_home', 'test' );
//
// add_filter( 'stylesheet_uri', 'test' );
// add_filter( 'stylesheet_directory', 'test' );
// add_filter( 'stylesheet_directory_uri', 'test' );
//
// add_filter( 'template_directory', 'test' );
// add_filter( 'template_directory_uri', 'test' );
// add_filter( 'plugins_url', 'test' );

function dark_matter_maybe_upgrade() {
  if ( is_network_admin() ) {
    $current_version = get_network_option( null, 'dark_matter_version', null );

    if ( null == $current_version || version_compare( DM_VERSION, $current_version, '>' ) ) {
      dark_matter_maybe_create_tables();
      update_network_option( null, 'dark_matter_version', DM_VERSION );
    }
  }
}

function dark_matter_plugins_loaded() {
  global $wpdb;

  $wpdb->dmtable = $wpdb->base_prefix . 'domain_mapping';

  dark_matter_maybe_upgrade();
}
add_action( 'plugins_loaded', 'dark_matter_plugins_loaded' );

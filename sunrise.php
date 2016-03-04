<?php

/** A bit of security for those who are too clever for their own good. */
defined( 'ABSPATH' ) or die();

if ( defined( 'COOKIE_DOMAIN' ) ) {
  wp_die( __( 'Multiple domain and sign-on is an interesting experience with a single ... defined domain ...', 'darkmatter' ) );
}

if ( false === defined( 'SUNRISE_LOADED' ) ) {
  define( 'SUNRISE_LOADED', true );
}

$dmtable = $wpdb->base_prefix . 'domain_mapping';
$domain = $_SERVER['HTTP_HOST'];
$dark_matter_sql = '';

if ( ( $domain_no_www = preg_replace( '|^www\.|', '', $domain ) ) !== $domain ) {
  $dark_matter_sql = $wpdb->prepare( "SELECT blog_id FROM {$dmtable} WHERE domain IN(%s, %s) LIMIT 1", $domain, $domain_no_www );
}
else {
  $dark_matter_sql = $wpdb->prepare( "SELECT blog_id FROM {$dmtable} WHERE domain = %s LIMIT 1", $domain );
}

$domain_mapping_id = $wpdb->get_var( $dark_matter_sql );

if ( false === empty( $domain_mapping_id ) ) {
  define( 'COOKIE_DOMAIN', $_SERVER[ 'HTTP_HOST' ] );
  define( 'DOMAIN_MAPPING', true );

  $current_blog = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->blogs WHERE blog_id = %d", $domain_mapping_id ) );
  $current_blog->original_domain = $current_blog->domain . $current_blog->path;
  $current_blog->domain = $_SERVER[ 'HTTP_HOST' ];
  $current_blog->path = '/';

  $blog_id = $domain_mapping_id;
  $site_id = $current_blog->site_id;

  $current_site = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->site} WHERE id = %s LIMIT 1", $site_id ) );
  $current_site->blog_id = $wpdb->get_var( $wpdb->prepare( "SELECT * FROM {$wpdb->blogs} WHERE domain = %s AND path = %s LIMIT 1", $current_site->domain, $current_site->path ) );
}

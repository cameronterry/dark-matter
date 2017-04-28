<?php

/** A bit of security for those who are too clever for their own good. */
defined( 'ABSPATH' ) or die();

if ( defined( 'COOKIE_DOMAIN' ) ) {
	wp_die( __( "Dark Matter's single sign on will not work if a COOKIE_DOMAIN is defined.", 'dark-matter' ) );
}

if ( false === defined( 'SUNRISE_LOADED' ) ) {
	define( 'SUNRISE_LOADED', true );
}

global $wpdb;

$dmtable = $wpdb->base_prefix . 'domain_mapping';
$domain = $_SERVER['HTTP_HOST'];
$dark_matter_sql = '';

/**
 * Get the Blog ID based on the provided Domain Name. This check is also done
 * for and without the www. sub-domain part.
 */
$dark_matter_sql = $wpdb->prepare( "SELECT * FROM {$dmtable} WHERE domain = %s LIMIT 1", $domain );

$mapped_domain = $wpdb->get_row( $dark_matter_sql );

/**
 * If the return value is not an integer (like NULL or FALSE), then that means
 * the domain hasn't been mapped with Dark Matter. If the return value is an
 * integer then we can proceed to construct the $current_blog global variable.
 */
if ( false === empty( $mapped_domain ) ) {
	/** Set the domain for the Cookie setting to the mapped domain. */
	define( 'COOKIE_DOMAIN', $_SERVER[ 'HTTP_HOST' ] );

	/** Set a constant to indicate that a mapped domain is in use. */
	define( 'DOMAIN_MAPPING', true );

	$current_blog = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->blogs WHERE blog_id = %d", $mapped_domain->blog_id ) );
	$current_blog->original_domain = $current_blog->domain . $current_blog->path;
	$current_blog->domain = $_SERVER[ 'HTTP_HOST' ];
	$current_blog->path = '/';
	$current_blog->https = boolval( $mapped_domain->is_https );

	$blog_id = $mapped_domain->blog_id;
	$site_id = $current_blog->site_id;

	$current_site = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->site} WHERE id = %s LIMIT 1", $site_id ) );
    $current_site->blog_id = $wpdb->get_var( $wpdb->prepare( "SELECT * FROM {$wpdb->blogs} WHERE domain = %s AND path = %s LIMIT 1", $current_site->domain, $current_site->path ) );
}

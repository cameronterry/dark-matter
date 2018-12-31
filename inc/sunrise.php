<?php

defined( 'ABSPATH' ) or die();

/**
 * If the COOKIE_DOMAIN is set, then we cannot proceed as the login will not be
 * able to function with the mapped domain.
 */
if ( defined( 'COOKIE_DOMAIN' ) ) {
    wp_die( __( "Dark Matter's single sign on will not work if a COOKIE_DOMAIN is defined.", 'dark-matter' ) );
}

wp_cache_add_global_groups( 'dark-matter' );

/**
 * Cannot utilise plugin_dir_path() as the inner function used is not available
 * and this is preferable to include more files than is realistically needed.
 */
$dirname = str_replace( '/inc', '', dirname( __FILE__ ) );

/**
 * Load the necessary parts of Dark Matter in to place.
 */
require_once $dirname . '/classes/DM_Domain.php';
require_once $dirname . '/classes/DarkMatter_Domains.php';

/**
 * Attempt to find the Site.
 */
$fqdn = $_SERVER['HTTP_HOST'];

$dm_domain = DarkMatter_Domains::instance()->get( $fqdn );

if ( ! $dm_domain ) {
    return;
}

/**
 * If the domain is not Primary, then we will attempt to find it and redirect.
 */
if ( ! $dm_domain->is_primary ) {
    require_once $dirname . '/classes/DarkMatter_Primary.php';

    $primary_domain = DarkMatter_Primary::instance()->get( $dm_domain->blog_id );

    /**
     * Redirect to the primary domain if we have one.
     */
    if ( $primary_domain ) {
        header( 'Location: http' . ( $primary_domain->is_https ? 's' : '' ) . '://' . $primary_domain->domain );
        die;
    }

    /**
     * If we do not have a primary domain, then we act like nothing has happened
     * and let WordPress' default behaviour take over.
     */
    return;
}

/**
 * Load and prepare the Blog data.
 */
global $current_blog;
$current_blog = get_site( $dm_domain->blog_id );

$current_blog->domain = $dm_domain->domain;
$current_blog->path   = '/';

/**
 * Load and prepare the WordPress Network.
 */
global $current_site;
$current_site = WP_Network::get_instance( $current_blog->site_id );

define( 'COOKIE_DOMAIN', $_SERVER[ 'HTTP_HOST' ] );
define( 'DOMAIN_MAPPING', true );

if ( empty( $current_site->blog_id ) ) {
    $current_site->blog_id = get_main_site_id( $current_site->id );
}

/**
 * Set the other necessary globals to ensure WordPress functions correctly.
 */
global $blog_id; $blog_id = $current_blog->blog_id;
global $site_id; $site_id = $current_blog->site_id;

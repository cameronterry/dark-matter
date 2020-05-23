<?php

defined( 'ABSPATH' ) or die();

wp_cache_add_global_groups( 'dark-matter' );

if ( false === defined( 'SUNRISE_LOADED' ) ) {
    define( 'SUNRISE_LOADED', true );
}

/**
 * Cannot utilise plugin_dir_path() as the inner function used is not available
 * and this is preferable to include more files than is realistically needed.
 */
$dirname = str_replace( '/inc', '', dirname( __FILE__ ) );

/**
 * Load the necessary parts of Dark Matter in to place.
 */
require_once $dirname . '/classes/DM_Domain.php';
require_once $dirname . '/api/DarkMatter_Domains.php';
require_once $dirname . '/api/DarkMatter_Primary.php';

/**
 * Attempt to find the Site.
 */
$fqdn = $_SERVER['HTTP_HOST'];

global $dm_domain;
$dm_domain = DarkMatter_Domains::instance()->get( $fqdn );

if ( $dm_domain && $dm_domain->active ) {
    /**
     * Prepare all the global variables. This is require irrespective of whether
     * it is a primary or secondary domain.
     */
    global $current_blog, $original_blog;
    $current_blog  = get_site( $dm_domain->blog_id );

    global $current_site;
    $current_site = WP_Network::get_instance( $current_blog->site_id );

    global $blog_id; $blog_id = $current_blog->blog_id;
    global $site_id; $site_id = $current_blog->site_id;

    /**
     * Dark Matter will disengage if the website is no longer public or is
     * archived or deleted.
     */
    if ( (int) $current_blog->public < 0 || $current_blog->archived !== '0' || $current_blog->deleted !== '0' ) {
        return;
    }

    /**
     * If the primary domain, then update the WP_Site properties to match the
     * mapped domain and not the admin domain.
     */
    if ( $dm_domain->is_primary ) {
        $original_blog = clone $current_blog;

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
    }
}

/**
 * Determine if we should perform a redirect.
 */
require_once $dirname . '/inc/redirect.php';

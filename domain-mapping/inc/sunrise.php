<?php
/**
 * Take the current request and perform the domain mapping for key global variables in WordPress.
 *
 * @package DarkMatter
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || die();

wp_cache_add_global_groups( 'dark-matter' );

if ( false === defined( 'SUNRISE_LOADED' ) ) {
	define( 'SUNRISE_LOADED', true );
}

define( 'DARKMATTER_COOKIE_SET', ! defined( 'COOKIE_DOMAIN' ) );

/**
 * Cannot utilise plugin_dir_path() as the inner function used is not available
 * and this is preferable to include more files than is realistically needed.
 */
$dirname = str_replace( '/inc', '', dirname( __FILE__ ) );

/**
 * Load the necessary parts of Dark Matter in to place.
 */
require_once $dirname . '/api/class-darkmatter-domains.php';
require_once $dirname . '/api/class-darkmatter-primary.php';

/**
 * Attempt to find the Site.
 */
$fqdn = ( empty( $_SERVER['HTTP_HOST'] ) ? '' : $_SERVER['HTTP_HOST'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

global $dm_domain;
$dm_domain = DarkMatter_Domains::instance()->get( $fqdn );

if ( $dm_domain && $dm_domain->active ) {
	/**
	 * Prepare all the global variables. This is require irrespective of whether
	 * it is a primary or secondary domain.
	 */
	global $current_blog, $original_blog;
	$current_blog = get_site( $dm_domain->blog_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

	global $current_site;
	$current_site = WP_Network::get_instance( $current_blog->site_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

    // @codingStandardsIgnoreStart
    global $blog_id;
    $blog_id = $current_blog->blog_id;
    global $site_id;
    $site_id = $current_blog->site_id;
    // @codingStandardsIgnoreEnd

	/**
	 * Dark Matter will disengage if the website is no longer public or is
	 * archived or deleted.
	 */
	if ( (int) $current_blog->public < 0 || '0' !== $current_blog->archived || '0' !== $current_blog->deleted ) {
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
		global $current_site; // phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.VariableRedeclaration
		$current_site = WP_Network::get_instance( $current_blog->site_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		if ( ! defined( 'COOKIE_DOMAIN' ) ) {
			define( 'COOKIE_DOMAIN', $dm_domain->domain );
		}

		define( 'DOMAIN_MAPPING', true );

		if ( empty( $current_site->blog_id ) ) {
			$current_site->blog_id = get_main_site_id( $current_site->id );
		}

		/**
		 * Set the other necessary globals to ensure WordPress functions correctly.
		 */
        // @codingStandardsIgnoreStart
        global $blog_id;
		$blog_id = $current_blog->blog_id;
        global $site_id;
		$site_id = $current_blog->site_id;
        // @codingStandardsIgnoreEnd
	}
}

/**
 * Determine if we should perform a redirect.
 */
require_once $dirname . '/inc/redirect.php';

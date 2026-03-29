<?php
/**
 * Handles the logic-side of `sunrise.php`, which is essentially to configure the global variables used for determining
 * the current site based on the request.
 *
 * @package DarkMatterPlugin
 *
 * @phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
 */

namespace DarkMatterPlugin\DomainMapping\Mapping;

use function DarkMatterPlugin\get_request_fqdn;
use function DarkMatterPlugin\is_site_public;

/**
 * Class Sunrise.
 */
class Sunrise {

	/**
	 * Constructor.
	 *
	 * @param string $plugin_path
	 */
	public function __construct( $plugin_path ) {
		$this->init( $plugin_path );

		$fqdn   = get_request_fqdn();
		$domain = \DarkMatter_Domains::instance()->get( $fqdn );
		if ( $domain instanceof \DM_Domain && $domain->active && $this->set_globals( $domain ) && $domain->is_primary ) {
			$this->map_globals( $domain );
		}
	}

	/**
	 * Ensures constants and caches common to Sunrise implementations are set.
	 *
	 * @return void
	 */
	public function init( $plugin_path ) {
		/**
		 * Legacy/backwards compatibility support. To be removed in v3.0.0.
		 */
		require_once $plugin_path . 'domain-mapping/classes/class-dm-domain.php';
		require_once $plugin_path . 'domain-mapping/api/class-darkmatter-domains.php';
		require_once $plugin_path . 'domain-mapping/api/class-darkmatter-primary.php';

		/**
		 * Without this, the cache entries for Dark Matter Plugin > Domain Mapping would be working on a site-by-site
		 * basis. This is a repeat of `dark-matter/dark-matter.php` plugin, which isn't loaded when this class/method
		 * is called.
		 */
		wp_cache_add_global_groups( 'dark-matter' );

		if ( false === defined( 'SUNRISE_LOADED' ) ) {
			define( 'SUNRISE_LOADED', true );
		}

		define( 'DARKMATTER_COOKIE_SET', ! defined( 'COOKIE_DOMAIN' ) );
	}

	/**
	 * Modify the globals to map the primary domain.
	 *
	 * @param \DM_Domain $primary Primary Domain data.
	 * @return void
	 */
	private function map_globals( $primary ) {
		/**
		 * Store the current WP_Site object in another global, so it is available for comparison and reference.
		 */
		global $current_blog, $original_blog;
		$original_blog = clone $current_blog;

		/**
		 * Update the domain and path to match the primary.
		 */
		$current_blog->domain = $primary->domain;
		$current_blog->path   = '/';

		/**
		 * Update the cookie domain to be the primary domain.
		 */
		if ( ! defined( 'COOKIE_DOMAIN' ) ) {
			define( 'COOKIE_DOMAIN', $primary->domain );
		}

		/**
		 * Set the constant to state the current request has been mapped.
		 */
		define( 'DOMAIN_MAPPING', true );
	}

	/**
	 * Sets up a few global variables which are used throughout WordPress Core. Will do some basic detection to ensure
	 * the website should be served on the mapped domain.
	 *
	 * @param \DM_Domain $domain Domain data.
	 * @return bool True for public domain, false otherwise.
	 */
	public function set_globals( $domain ) {
		global $blog_id, $current_blog, $current_site, $site_id;

		/**
		 * Set the current Blog (WP_Site) and current Site (WP_Network).
		 */
		$current_blog = get_site( $domain->blog_id );
		$blog_id      = $current_blog->blog_id;
		$current_site = \WP_Network::get_instance( $current_blog->site_id );
		$site_id      = $current_blog->site_id;

		return is_site_public( $current_blog );
	}
}

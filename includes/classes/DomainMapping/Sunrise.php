<?php
/**
 * Handles the logic for WordPress dropin plugin, `sunrise.php`.
 *
 * @since 3.0.0
 *
 * @package DarkMatterPlugin\DomainMapping
 *
 * @phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
 */

namespace DarkMatter\DomainMapping;

use DarkMatter\DomainMapping\Data;
use DarkMatter\DomainMapping\Manager;
use DarkMatter\DomainMapping\Processor\Redirect;

/**
 * Class Sunrise
 *
 * @since 3.0.0
 */
class Sunrise {
	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->init();

		/**
		 * Find the domain based on the request.
		 */
		$domain = $this->get_domain();
		if ( $domain && $domain->active && $this->set_globals( $domain ) && $domain->is_primary ) {
			$this->update_globals( $domain );
		}

		/**
		 * Hook up the redirect logic.
		 */
		$redirect = new Redirect();
		$redirect->register();
	}

	/**
	 * Get the domain from the requested FQDN.
	 *
	 * @since 3.0.0
	 *
	 * @return bool|Data\Domain
	 */
	private function get_domain() {
		$fqdn = Helper::instance()->get_request_fqdn();
		return Manager\Domain::instance()->get( $fqdn );
	}

	/**
	 * Set up various globals and constants which are used, either by Dark Matter itself or by other plugins and/or
	 * WordPress Core.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function init() {
		/**
		 * Ensure `dark-matter` cache group is available globally.
		 */
		wp_cache_add_global_groups( 'dark-matter' );

		/**
		 * Define the `SUNRISE_LOADED`. Dark Matter Plugin does not make use of this but other plugins do in certain
		 * circumstances (i.e. Jetpack). So we define it for compatibility.
		 */
		if ( false === defined( 'SUNRISE_LOADED' ) ) {
			define( 'SUNRISE_LOADED', true );
		}

		/**
		 * Use to detect misconfiguration where the cookie domain is set.
		 */
		define( 'DARKMATTER_COOKIE_SET', ! defined( 'COOKIE_DOMAIN' ) );
	}

	/**
	 * Sets up a few global variables which are used through WordPress Core. Will do some basic detection to ensure the
	 * website should be served on the mapped domain.
	 *
	 * @since 3.0.0
	 *
	 * @param Data\Domain $domain Domain data.
	 * @return bool True to proceed, false to disengage.
	 */
	private function set_globals( $domain ) {
		global $blog_id, $current_blog, $current_site, $site_id;

		/**
		 * Set the current Blog (WP_Site) and current Site (WP_Network).
		 */
		$current_blog = get_site( $domain->blog_id );
		$blog_id      = $current_blog->blog_id;
		$current_site = \WP_Network::get_instance( $current_blog->site_id );
		$site_id      = $current_blog->site_id;

		return Helper::instance()->is_public( $current_blog );
	}

	/**
	 * Prepare WordPress globals to use the primary domain data instead.
	 *
	 * @since 3.0.0
	 *
	 * @param Data\Domain $primary Primary Domain data.
	 * @return void
	 */
	private function update_globals( $primary ) {
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
}

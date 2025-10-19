<?php
/**
 * Perform the initial loading of the Domain Mapping feature.
 *
 * @package DarkMatterPlugin
 */

namespace DarkMatterPlugin\DomainMapping;

use DarkMatterPlugin\Registerable;

/**
 * Class DomainMapping
 */
class DomainMapping implements Registerable {

	/**
	 * Register the DomainMapping Feature.
	 *
	 * @return true
	 */
	public function can_register() {
		return true;
	}

	/**
	 * Handle actions and filters for DomainMapping Feature.
	 *
	 * @return void
	 */
	public function register() {
		$this->register_legacy();
	}

	/**
	 * This loads the legacy classes that custom code will likely be referencing. This needs to stay in place until
	 * v3.0.0 of Dark Matter Plugin is release and backwards compatibility with v2.* will be formally ended.
	 *
	 * @return void
	 */
	public function register_legacy() {
		/**
		 * Domain type constants.
		 */
		define( 'DM_DOMAIN_TYPE_MAIN', 1 );
		define( 'DM_DOMAIN_TYPE_MEDIA', 2 );

		require_once DM_PATH . '/domain-mapping/classes/class-dm-media.php';
		require_once DM_PATH . '/domain-mapping/classes/class-dm-domain.php';
		require_once DM_PATH . '/domain-mapping/classes/class-dm-healthchecks.php';
		require_once DM_PATH . '/domain-mapping/classes/class-dm-url.php';

		require_once DM_PATH . '/domain-mapping/api/class-darkmatter-domains.php';
		require_once DM_PATH . '/domain-mapping/api/class-darkmatter-primary.php';
		require_once DM_PATH . '/domain-mapping/api/class-darkmatter-restrict.php';
	}
}

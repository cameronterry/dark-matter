<?php
/**
 * Domain Mapping root file - used to include all the relevant files, etc., related to domain mapping functionality.
 *
 * @package DarkMatter
 *
 * @since 2.2.0
 */

defined( 'ABSPATH' ) || die;

/**
 * Domain type constants.
 */
define( 'DM_DOMAIN_TYPE_MAIN', 1 );
define( 'DM_DOMAIN_TYPE_MEDIA', 2 );

require_once DM_PATH . '/domain-mapping/inc/compat.php';

require_once DM_PATH . '/domain-mapping/classes/class-dm-healthchecks.php';

/**
 * Plugin compatibility.
 */
require_once DM_PATH . '/domain-mapping/classes/third-party/class-dm-yoast.php';

require_once DM_PATH . '/domain-mapping/api/class-darkmatter-domains.php';
require_once DM_PATH . '/domain-mapping/api/class-darkmatter-primary.php';
require_once DM_PATH . '/domain-mapping/api/class-darkmatter-restrict.php';

/**
 * Disable SSO if the COOKIE_DOMAIN constant is set.
 */
if ( DM_HealthChecks::instance()->cookie_domain_dm_set() && ( ! defined( 'DARKMATTER_SSO_TYPE' ) || 'disable' !== DARKMATTER_SSO_TYPE ) ) {
	require_once DM_PATH . '/domain-mapping/sso/class-dm-sso-cookie.php';
}

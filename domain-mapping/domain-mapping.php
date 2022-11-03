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

/**
 * Disable SSO if the COOKIE_DOMAIN constant is set.
 */
if ( ! defined( 'DARKMATTER_SSO_TYPE' ) || 'disable' !== DARKMATTER_SSO_TYPE ) {
	require_once DM_PATH . '/domain-mapping/sso/class-dm-sso-cookie.php';
}

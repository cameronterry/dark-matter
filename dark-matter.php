<?php
/**
 * Plugin Name: Dark Matter
 * Plugin URI: https://github.com/cameronterry/dark-matter
 * Description: A highly opinionated domain mapping plugin for WordPress.
 * Version: 2.1.7
 * Author: Cameron Terry
 * Author URI: https://github.com/cameronterry/
 * Text Domain: dark-matter
 * Network: True
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * GitHub Plugin URI: https://github.com/cameronterry/dark-matter
 *
 * Dark Matter is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Dark Matter is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Dark Matter. If not, see
 * https://github.com/cameronterry/dark-matter/blob/master/license.txt.
 *
 * @package DarkMatter
 */

/** A bit of security for those who are too clever for their own good. */
defined( 'ABSPATH' ) || die;

/** Setup the Plugin Constants */
define( 'DM_PATH', plugin_dir_path( __FILE__ ) );
define( 'DM_VERSION', '2.1.8' );
define( 'DM_DB_VERSION', '20190114' );

define( 'DM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

wp_cache_add_global_groups( 'dark-matter' );

require_once DM_PATH . '/domain-mapping/inc/compat.php';

require_once DM_PATH . '/domain-mapping/classes/class-dm-database.php';
require_once DM_PATH . '/domain-mapping/classes/class-dm-domain.php';
require_once DM_PATH . '/domain-mapping/classes/class-dm-healthchecks.php';
require_once DM_PATH . '/domain-mapping/classes/class-dm-url.php';

/**
 * Plugin compatibility.
 */
require_once DM_PATH . '/domain-mapping/classes/third-party/class-dm-yoast.php';

if ( ! defined( 'DARKMATTER_HIDE_UI' ) || ! DARKMATTER_HIDE_UI ) {
	require_once DM_PATH . '/domain-mapping/classes/class-dm-ui.php';
}

require_once DM_PATH . '/domain-mapping/api/class-darkmatter-domains.php';
require_once DM_PATH . '/domain-mapping/api/class-darkmatter-primary.php';
require_once DM_PATH . '/domain-mapping/api/class-darkmatter-restrict.php';

/**
 * Disable SSO if the COOKIE_DOMAIN constant is set.
 */
if ( DM_HealthChecks::instance()->cookie_domain_dm_set() && ( ! defined( 'DARKMATTER_SSO_TYPE' ) || 'disable' !== DARKMATTER_SSO_TYPE ) ) {
	require_once DM_PATH . '/domain-mapping/sso/class-dm-sso-cookie.php';
}

require_once DM_PATH . '/domain-mapping/rest/class-dm-rest-domains-controller.php';
require_once DM_PATH . '/domain-mapping/rest/class-dm-rest-restricted-controller.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once DM_PATH . '/domain-mapping/cli/class-darkmatter-domain-cli.php';
	require_once DM_PATH . '/domain-mapping/cli/class-darkmatter-dropin-cli.php';
	require_once DM_PATH . '/domain-mapping/cli/class-darkmatter-restrict-cli.php';
}

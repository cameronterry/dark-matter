<?php
/**
 * Plugin Name: Dark Matter
 * Plugin URI: https://cameronterry.supernovawp.com/dark-matter/
 * Description: A domain mapping plugin from Project Dark Matter.
 * Version: 2.0.5
 * Author: Cameron Terry
 * Author URI: https://cameronterry.co.uk/
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
 */

/** A bit of security for those who are too clever for their own good. */
defined( 'ABSPATH' ) || die;

/** Setup the Plugin Constants */
define( 'DM_PATH', plugin_dir_path( __FILE__ ) );
define( 'DM_VERSION', '2.0.5' );
define( 'DM_DB_VERSION', '20190114' );

define( 'DM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

wp_cache_add_global_groups( 'dark-matter' );

require_once DM_PATH . '/domain-mapping/inc/compat.php';

require_once DM_PATH . '/domain-mapping/classes/DM_Database.php';
require_once DM_PATH . '/domain-mapping/classes/DM_Domain.php';
require_once DM_PATH . '/domain-mapping/classes/DM_URL.php';

if ( ! defined( 'DARKMATTER_HIDE_UI' ) || ! DARKMATTER_HIDE_UI ) {
    require_once DM_PATH . '/domain-mapping/classes/DM_UI.php';
}

require_once DM_PATH . '/domain-mapping/api/DarkMatter_Domains.php';
require_once DM_PATH . '/domain-mapping/api/DarkMatter_Primary.php';
require_once DM_PATH . '/domain-mapping/api/DarkMatter_Restrict.php';

if ( ! defined( 'DARKMATTER_SSO_TYPE' ) || 'disable' !== DARKMATTER_SSO_TYPE ) {
    require_once DM_PATH . '/domain-mapping/sso/DM_SSO_Cookie.php';
}

require_once DM_PATH . '/domain-mapping/rest/DM_REST_Domains_Controller.php';
require_once DM_PATH . '/domain-mapping/rest/DM_REST_Restricted_Controller.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    require_once DM_PATH . '/domain-mapping/cli/domain.php';
    require_once DM_PATH . '/domain-mapping/cli/restrict.php';
    require_once DM_PATH . '/domain-mapping/cli/update.php';
}
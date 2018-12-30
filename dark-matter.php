<?php
/**
 * Plugin Name: Dark Matter
 * Plugin URI: https://cameronterry.supernovawp.com/dark-matter/
 * Description: A domain mapping plugin from Project Dark Matter.
 * Version: 2.0.0
 * Author: Cameron Terry
 * Author URI: https://cameronterry.co.uk/
 * Text Domain: dark-matter
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
define( 'DM_VERSION', '2.0.0' );
define( 'DM_DB_VERSION', '20181230' );

require_once DM_PATH . '/classes/DM_Database.php';
require_once DM_PATH . '/classes/DM_Domain.php';

require_once DM_PATH . '/classes/DarkMatter_Domains.php';
require_once DM_PATH . '/classes/DarkMatter_Primary.php';
require_once DM_PATH . '/classes/DarkMatter_Reserve.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    require_once DM_PATH . '/cli/domain.php';
    require_once DM_PATH . '/cli/reserve.php';
}
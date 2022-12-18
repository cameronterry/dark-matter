<?php
defined( 'ABSPATH' ) || die;

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
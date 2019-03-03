<?php

defined( 'ABSPATH' ) || die;

class DarkMatter_Dropin_CLI {
    /**
     * Upgrade the Sunrise dropin plugin to the latest version within the Dark
     * Matter plugin.
     *
     * ### EXAMPLES
     * Update the sunrise.php dropin plugin.
     *
     *      wp darkmatter dropin update
     */
    public function update( $args, $assoc_args ) {
        $destination = WP_CONTENT_DIR . '/sunrise.php';
        $source      = DM_PATH . '/domain-mapping/sunrise.php';
    }
}
WP_CLI::add_command( 'darkmatter dropin', 'DarkMatter_Dropin_CLI' );
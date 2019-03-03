<?php

defined( 'ABSPATH' ) || die;

class DarkMatter_Dropin_CLI {
    /**
     * Upgrade the Sunrise dropin plugin to the latest version within the Dark
     * Matter plugin.
     *
     * ### OPTIONS
     *
     * [--force]
     * : Force Dark Matter to override and update Sunrise dropin if a file
     * already exists.
     *
     * ### EXAMPLES
     * Install the Sunrise dropin plugin for new installations.
     *
     *      wp darkmatter dropin update
     *
     * Update the Sunrise dropin plugin, even if a file is already present.
     *
     *      wp darkmatter dropin update --force
     */
    public function update( $args, $assoc_args ) {
        $destination = WP_CONTENT_DIR . '/sunrise.php';
        $source      = DM_PATH . '/domain-mapping/sunrise.php';
    }
}
WP_CLI::add_command( 'darkmatter dropin', 'DarkMatter_Dropin_CLI' );
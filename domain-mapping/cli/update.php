<?php

defined( 'ABSPATH' ) || die;

class DarkMatter_Dropin_CLI {
    /**
     * Helper command to see if the Sunrise dropin plugin within Dark Matter is
     * the same version as in use on the current WordPress installation.
     *
     * ### Examples
     * Check to see if the Sunrise dropin is the latest version.
     *
     *      wp darkmatter dropin check
     */
    public function check( $args, $assoc_args ) {
        $destination = WP_CONTENT_DIR . '/sunrise.php';
        $source      = DM_PATH . '/domain-mapping/sunrise.php';

        if (
            filesize( $destination ) === filesize( $source )
        &&
            md5_file( $destination ) === md5_file( $source )
        ) {
            WP_CLI::success( __( 'Current Sunrise dropin matches the Sunrise within Dark Matter plugin.', 'dark-matter' ) );
            return;
        }

        WP_CLI::error( __( 'Sunrise dropin does not match the Sunrise within Dark Matter plugin. Consider using the "update" command to correct this issue.', 'dark-matter' ) );
    }

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

        $opts = wp_parse_args( $assoc_args, [
            'force'   => false,
        ] );

        if ( false === is_writable( WP_CONTENT_DIR ) ) {
            WP_CLI::error( __( 'The /wp-content/ directory needs to be writable by the current user in order to update.', 'dark-matter' ) );
        }

        if ( file_exists( $destination ) && false === $opts['force'] ) {
            WP_CLI::error( __( 'Sunrise is already present. Use the --force flag to override.', 'dark-matter' ) );
        }

        if ( false === is_readable( $source ) ) {
            WP_CLI::error( __( 'Cannot read the Sunrise dropin within the Dark Matter plugin folder.', 'dark-matter' ) );
        }

        if ( false === file_exists( $source ) ) {
            WP_CLI::error( __( 'Sunrise dropin within the Dark Matter plugin is missing.', 'dark-matter' ) );
        }

        if ( @copy( $source, $destination ) ) {
            WP_CLI::success( __( 'Updated the Sunrise dropin to the latest version.', 'dark-matter' ) );
        } else {
            WP_CLI::error( __( 'Unknown error occurred preventing the update of Sunrise dropin.', 'dark-matter' ) );
        }
    }
}
WP_CLI::add_command( 'darkmatter dropin', 'DarkMatter_Dropin_CLI' );
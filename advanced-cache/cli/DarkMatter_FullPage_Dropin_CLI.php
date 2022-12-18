<?php
defined( 'ABSPATH' ) || die();

class DarkMatter_FullPage_Dropin_CLI {
    /**
     * Helper command to see if the Sunrise dropin plugin within Dark Matter is the same version as in use on the
     * current WordPress installation.
     *
     * ### Examples
     * Check to see if the Sunrise dropin is the latest version.
     *
     *      wp darkmatter fullpage dropin check
     */
    public function check( $args, $assoc_args ) {
        $destination = WP_CONTENT_DIR . '/advanced-cache.php';
        $source      = DM_PATH . '/advanced-cache/advanced-cache.php';

        if (
            filesize( $destination ) === filesize( $source )
            &&
            md5_file( $destination ) === md5_file( $source )
        ) {
            WP_CLI::success( __( 'Current advanced-cache.php dropin matches the Full Page Cache within Dark Matter plugin.', 'dark-matter' ) );
            return;
        }

        WP_CLI::error( __( 'advanced-cache.php dropin does not match the Full Page Cache within Dark Matter plugin. Consider using the "update" command to correct this issue.', 'dark-matter' ) );
    }

    /**
     * Upgrade the Full Page Cache dropin plugin to the latest version within the Dark Matter plugin.
     *
     * ### OPTIONS
     *
     * [--force]
     * : Force Dark Matter to override and update Full Page Cache dropin if a file already exists.
     *
     * ### EXAMPLES
     * Install the Full Page Cache dropin plugin for new installations.
     *
     *      wp darkmatter fullpage dropin update
     *
     * Update the Full Page Cache dropin plugin, even if a file is already present.
     *
     *      wp darkmatter fullpage dropin update --force
     */
    public function update( $args, $assoc_args ) {
        $destination = WP_CONTENT_DIR . '/advanced-cache.php';
        $source      = DM_PATH . '/advanced-cache/advanced-cache.php';

        $opts = wp_parse_args( $assoc_args, [
            'force'   => false,
        ] );

        if ( false === is_writable( WP_CONTENT_DIR ) ) {
            WP_CLI::error( __( 'The /wp-content/ directory needs to be writable by the current user in order to update.', 'dark-matter' ) );
        }

        if ( false === $opts['force'] && file_exists( $destination ) ) {
            WP_CLI::error( __( 'advanced-cache.php is already present. Use the --force flag to override.', 'dark-matter' ) );
        }

        if ( false === is_readable( $source ) ) {
            WP_CLI::error( __( 'Cannot read the advanced-cache.php dropin within the Dark Matter plugin folder.', 'dark-matter' ) );
        }

        if ( false === file_exists( $source ) ) {
            WP_CLI::error( __( 'advanced-cache.php dropin within the Dark Matter plugin is missing.', 'dark-matter' ) );
        }

        if ( @copy( $source, $destination ) ) {
            WP_CLI::success( __( 'Updated the advanced-cache.php dropin to the latest version.', 'dark-matter' ) );
        } else {
            WP_CLI::error( __( 'Unknown error occurred preventing the update of Full Page Cache dropin.', 'dark-matter' ) );
        }
    }
}
WP_CLI::add_command( 'darkmatter fullpage dropin', 'DarkMatter_FullPage_Dropin_CLI' );
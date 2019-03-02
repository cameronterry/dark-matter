<?php
/**
 * Helper file to provide basic compatibilty for some popular plugins.
 */
defined( 'ABSPATH' ) or die();

/**
 * Undocumented function
 *
 * @return void
 */
function dark_matter_compat_allow_logins() {
    if (
        /**
         * Detect if WooCommerce is installed as this needs to allow logins.
         */
        class_exists( 'WooCommerce' )
    ) {
        add_filter( 'darkmatter_allow_logins', '__return_true' );
    }
}
add_action( 'init', 'dark_matter_compat_allow_logins' );
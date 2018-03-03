<?php
/** A bit of security for those who are too clever for their own good. */
defined( 'ABSPATH' ) or die();

function dark_matter_sunrise_nag() {
    if ( is_super_admin() ) : ?>
        <div class="notice notice-warning">
            <p><?php _e( 'The SUNRISE constant does not appear to be defined in your wp-config.php file. This will cause Dark Matter to not function correctly and possibly cause unknown issues with domain mapping. Please ensure you have SUNRISE constant defined in your wp-config.php file.', 'sample-text-domain' ); ?></p>
        </div>
    <?php endif;
}
if ( false === defined( 'SUNRISE' ) ) {
    add_action( 'admin_notices', 'dark_matter_sunrise_nag' );
}

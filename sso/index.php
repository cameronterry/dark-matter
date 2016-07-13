<?php

defined( 'ABSPATH' ) or die();

function dark_matter_sso_wp_head() {
	if ( false === is_user_logged_in() && false === is_main_site() ) : ?>
	<script type="text/javascript" src="<?php echo( network_site_url( '/sso/' ) ); ?>"></script>
<?php endif;
}
add_action( 'wp_head', 'dark_matter_sso_wp_head' );

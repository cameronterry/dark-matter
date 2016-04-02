<?php

/**
 * Links:
 * - http://meta.stackexchange.com/questions/64260/how-does-sos-new-auto-login-feature-work/64274#64274
 * - http://stackoverflow.com/questions/342378/cross-domain-login-how-to-login-a-user-automatically-when-transferred-from-one
 * - https://github.com/markoheijnen/wordpress-mu-domain-mapping/blob/master/domain_mapping.php#L758-L814
 */
function dark_matter_sso_endpoint() {
  add_rewrite_endpoint( 'sso', EP_ALL );
}
add_action( 'init', 'dark_matter_sso_endpoint' );

function dark_matter_sso_template() {
  global $wp_query;

  if ( array_key_exists( 'sso', $wp_query->query_vars ) ) {
    header( 'Content-Type: application/javascript' );
    header( 'Etag: hello-world' );
    exit;
  }
}
add_action( 'template_redirect', 'dark_matter_sso_template' );

function dark_matter_sso_wp_head() { ?>
  <script type="text/javascript" src="http://wordpressnetwork.test/sso/"></script>
<?php }
add_action( 'wp_head', 'dark_matter_sso_wp_head' );

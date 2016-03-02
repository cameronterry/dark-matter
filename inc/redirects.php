<?php

function dark_matter_admin_redirect() {
  /**
   * Do not redirect the AJAX calls. It is part of the admin but not
   * the part we wish to put redirects on.  Especially for Front-end
   * AJAX functionality.
   */
  if ( false !== strpos( $_SERVER['REQUEST_URI'], 'wp-admin/admin-ajax.php' ) ) {
    return;
  }

  /**
   * Also do not redirect the Cron URL.
   */
  if ( false !== strpos( $_SERVER['REQUEST_URI'], 'wp-cron.php' ) ) {
    return;
  }

  global $current_blog;

  if ( false === empty( $current_blog->original_domain ) && false === strpos( $current_blog->original_domain, $_SERVER[ 'HTTP_HOST' ] ) ) {
    $protocol = ( is_ssl() ? 'https://' : 'http://' );
    $protocol = ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https://' : $protocol );

    $domain = untrailingslashit( $current_blog->original_domain );
    $request = $_SERVER['REQUEST_URI'];

    wp_redirect( sprintf( '%1$s%2$s%3$s', $protocol, $domain, $request ) );
    exit;
  }
}
add_action( 'admin_init', 'dark_matter_admin_redirect' );
add_action( 'login_init', 'dark_matter_admin_redirect' );

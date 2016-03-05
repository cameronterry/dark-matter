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
  $original_domain = dark_matter_api_get_domain_original();

  if ( false === empty( $original_domain ) && false === strpos( $original_domain, $_SERVER[ 'HTTP_HOST' ] ) ) {
    $protocol = ( is_ssl() ? 'https://' : 'http://' );
    $protocol = ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https://' : $protocol );

    $domain = untrailingslashit( $original_domain );
    $request = $_SERVER['REQUEST_URI'];

    wp_redirect( sprintf( '%1$s%2$s%3$s', $protocol, $domain, $request ) );
    exit;
  }
}
add_action( 'admin_init', 'dark_matter_admin_redirect' );
add_action( 'login_init', 'dark_matter_admin_redirect' );

function dark_matter_frontend_redirect() {
  /** If it's the main site in the network, do not redirect. */
  if ( is_main_site() || is_preview() ) {
    return;
  }

  /** If we are viewing in the WP Customizer, then we don't want to redirect either. */
  global $wp_customize;

  if ( is_a( $wp_customize, 'WP_Customize_Manager' ) ) {
    return;
  }

  global $current_blog, $wpdb;

  $primary_domain = $wpdb->get_var( $wpdb->prepare( "SELECT domain FROM {$wpdb->dmtable} WHERE blog_id = %s", get_current_blog_id() ) );

  /** No domain has been mapped. */
  if ( empty( $primary_domain ) ) {
    return;
  }

  if ( $primary_domain === $_SERVER['HTTP_HOST'] ) {
    return;
  }

  $scheme = ( is_ssl() ? 'https://' : 'http://' );
  $path = str_replace( $current_blog->path, '', $_SERVER['REQUEST_URI'] );
  $redirect_url = sprintf( '%1$s%2$s/%3$s', $scheme, $primary_domain, $path );

  wp_redirect( $redirect_url );
  return;
}
add_action( 'template_redirect', 'dark_matter_frontend_redirect' );

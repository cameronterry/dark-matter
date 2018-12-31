<?php

defined( 'ABSPATH' ) or die();

if (
        /**
         * Do not attempt to redirect during the CLI command.
         */
        defined( 'WP_CLI' ) && WP_CLI
    ||
        /**
         * Do not attempt to redirect during the execution of cron.
         */
        defined( 'DOING_CRON' ) && DOING_CRON
    ||
        /**
         * Do not redirect Admin area. For note; this returns false if the
         * unmapped URL is called.
         */
        is_admin()
) {
    return;
}

/**
 * Helper function which is used to determine if we need to perform a redirect
 * to the primary domain whilst retaining the remaining URI structure.
 *
 * @return void
 */
function darkmatter_maybe_redirect() {
    global $dm_original_path;

    /**
     * Retrieve the original blog details. We use this technique rather than
     * some thing pre-loaded from sunrise.php as not all requests will have
     * populated the global variables by this point. For instance; if you call a
     * URL on the unmapped domain, then the globals will be empty.
     */
    $original_blog = get_site( get_current_blog_id() );

    $host    = trim( $_SERVER['HTTP_HOST'], '/' );
    $request = ltrim( $_SERVER['REQUEST_URI'], '/' );
    $primary = DarkMatter_Primary::instance()->get();

    if ( $host !== $primary->domain || is_ssl() === $primary->is_https ) {
        $url = 'http' . ( $primary->is_https ? 's' : '' ) . '://' . $primary->domain . '/' . $request;
    }

    /**
     * If the URL is empty, then there is no redirect to perform.
     */
    if ( empty( $url ) ) {
        return;
    }

    /**
     * Make sure the Path - if this is a sub-folder Network - is removed from
     * the URL. For sub-domain Networks, the path will be a single forward slash
     * (/).
     */
    if ( '/' !== $original_blog->path ) {
        $path = '/' . trim( $original_blog->path, '/' ) . '/';
        $url  = str_ireplace( $path, '/', $url );
    }

    var_dump( $url );
    die;
}

/**
 * We use "muplugins_loaded" action (introduced in WordPress 2.8.0) rather than
 * the "ms_loaded" (introduced in WordPress 4.6.0).
 */
add_action( 'muplugins_loaded', 'darkmatter_maybe_redirect' );
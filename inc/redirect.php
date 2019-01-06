<?php

defined( 'ABSPATH' ) or die();

/**
 * The function `rest_get_url_prefix()` is not available at this point in the
 * load process. Therefore we must substitute it with a close approximate of
 * what the function does.
 *
 * There is a side-effect of this. Basically if a site is to be setup with a
 * different prefix, in order for this to work, the `add_filter()` call would
 * need to be done in an Must-Use Plugin.
 */
$rest_url_prefix = '/' . trim( apply_filters( 'rest_url_prefix', 'wp-json' ), '/' ) . '/';

/**
 * Stop the execution of PHP code if any of the following conditions are true.
 * The constants noted below are all available and prior to the loading of the
 * sunrise.php file.
 */
if (
        /**
         * Do not attempt to redirect during the CLI command.
         */
        defined( 'WP_CLI' ) && WP_CLI
    ||
        /**
         * AJAX requests can be used on both the mapped and unmapped domains.
         */
        defined( 'DOING_AJAX' ) && DOING_AJAX
    ||
        /**
         * Do not attempt to redirect during the execution of cron.
         */
        defined( 'DOING_CRON' ) && DOING_CRON
    ||
        /**
         * XMLRPC Requests can be used on both the mapped and unmapped domains.
         */
        defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST
    ||
        /**
         * REST API can be used on both the mapped and unmapped domains.
         */
        false !== strpos( $_SERVER['REQUEST_URI'], $rest_url_prefix )
    ||
        /**
         * Do not redirect Admin area. For note; this returns false if the
         * unmapped URL is called.
         */
        is_admin()
    ||
        /**
         * Customizer is presented in an <iframe> over the unmapped domain.
         */
        ! empty( $_GET['customize_changeset_uuid'] )
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
    /**
     * Do not perform redirects if it is the main site.
     */
    if ( is_main_site() ) {
        return;
    }

    $request = ltrim( $_SERVER['REQUEST_URI'], '/' );

    /**
     * Determine if the request is one we shouldn't handle for redirects.
     */
    $filename = basename( $request );
    $filename = strtok( $filename, '?' );

    if ( in_array( $filename, array( 'wp-login.php', 'wp-register.php' ) ) ) {
        return;
    }

    $original_blog = get_site();

    $host    = trim( $_SERVER['HTTP_HOST'], '/' );
    $primary = DarkMatter_Primary::instance()->get();

    /**
     * If there is no primary domain, there is nothing to do.
     */
    if ( ! $primary || ! $original_blog ) {
        return;
    }

    if ( $host !== $primary->domain || is_ssl() !== $primary->is_https ) {
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

    header( 'Location:' . $url, true, 301 );
    die;
}

/**
 * We use "muplugins_loaded" action (introduced in WordPress 2.8.0) rather than
 * the "ms_loaded" (introduced in WordPress 4.6.0).
 */
add_action( 'muplugins_loaded', 'darkmatter_maybe_redirect', 20 );
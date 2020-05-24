<?php
/**
 * Redirect
 *
 * @package DarkMatter
 */

defined( 'ABSPATH' ) || die();

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
        ( defined( 'WP_CLI' ) && WP_CLI )
    ||
        /**
         * AJAX requests can be used on both the mapped and unmapped domains.
         */
        ( defined( 'DOING_AJAX' ) && DOING_AJAX )
    ||
        /**
         * Do not attempt to redirect during the execution of cron.
         */
        ( defined( 'DOING_CRON' ) && DOING_CRON )
    ||
        /**
         * XMLRPC Requests can be used on both the mapped and unmapped domains.
         */
        ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
    ||
        /**
         * REST API can be used on both the mapped and unmapped domains.
         */
        ( ! empty( $_SERVER['REQUEST_URI'] ) && false !== strpos( $_SERVER['REQUEST_URI'], $rest_url_prefix ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    ||
        /**
         * Customizer is presented in an <iframe> over the unmapped domain.
         */
        ! empty( $_GET['customize_changeset_uuid'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    ||
        /**
         * Do not redirect Previews
         */
        ( ! empty( $_GET['preview'] ) || ! empty( $_GET['page_id'] ) || ! empty( $_GET['p'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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

    $request_uri = ( empty( $_SERVER['REQUEST_URI'] ) ? '' : filter_var( $_SERVER['REQUEST_URI'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW ) );

    $request = ltrim( $request_uri, '/' );

    /**
     * Determine if the request is one we shouldn't handle for redirects.
     */
    $filename = basename( $request );
    $filename = strtok( $filename, '?' );

    /**
     * Check to see if the current request is an Admin Post action or an AJAX action. These two requests in Dark Matter
     * can be on either the admin domain or the primary domain.
     */
    if ( in_array( $filename, array( 'admin-post.php', 'admin-ajax.php' ), true ) ) {
        return;
    }

    $original_blog = get_site();

    $http_host = ( empty( $_SERVER['HTTP_HOST'] ) ? '' : filter_var( $_SERVER['HTTP_HOST'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW ) );

    $host    = trim( $http_host, '/' );
    $primary = DarkMatter_Primary::instance()->get();

    $is_admin = false;

    if ( is_admin() || in_array( $filename, array( 'wp-login.php', 'wp-register.php' ) ) ) {
        $is_admin = true;
    }

    /**
     * Dark Matter will disengage if the website is no longer public or is archived or deleted.
     */
    if ( (int) $original_blog->public < 0 || '0' !== $original_blog->archived || '0' !== $original_blog->deleted ) {
        return;
    }

    /**
     * If Allow Logins is enabled, then the `wp-login.php` request is to be made
     * available on both the primary mapped domain and admin domain.
     */
    if ( ! apply_filters( 'darkmatter_allow_logins', false ) && $is_admin && $host === $original_blog->domain ) {
        return;
    }

    /**
     * If there is no primary domain, there is nothing to do. Also make sure the
     * domain is active.
     */
    if ( ! $primary || ! $original_blog || ! $primary->active || absint( $original_blog->public ) < 1 ) {
        return;
    }

    if ( $is_admin && $host !== $original_blog->domain ) {
        $is_ssl_admin = ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN );

        $url = 'http' . ( $is_ssl_admin ? 's' : '' ) . '://' . $original_blog->domain . $original_blog->path . $request;
    } elseif ( $host !== $primary->domain || is_ssl() !== $primary->is_https ) {
        $url = 'http' . ( $primary->is_https ? 's' : '' ) . '://' . $primary->domain . '/' . $request;

        /**
         * Make sure the Path - if this is a sub-folder Network - is removed from
         * the URL. For sub-domain Networks, the path will be a single forward slash
         * (/).
         */
        if ( '/' !== $original_blog->path ) {
            $path = '/' . trim( $original_blog->path, '/' ) . '/';
            $url  = str_ireplace( $path, '/', $url );
        }
    }

    /**
     * If the URL is empty, then there is no redirect to perform.
     */
    if ( empty( $url ) ) {
        return;
    }

    header( 'X-Redirect-By: Dark-Matter' );
    header( 'Location:' . $url, true, 301 );

    die;
}

/**
 * We use `muplugins_loaded` action (introduced in WordPress 2.8.0) rather than
 * the "ms_loaded" (introduced in WordPress 4.6.0).
 *
 * A hook on `muplugins_loaded` is used to ensure that WordPress has loaded the
 * Blog / Site globals. This is specifically useful when some one goes to the
 * Admin domain URL - http://my.sites.com/two/ - which is to redirect to the
 * primary domain - http://example.com.
 */
add_action( 'muplugins_loaded', 'darkmatter_maybe_redirect', 20 );

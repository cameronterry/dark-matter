<?php

defined( 'ABSPATH' ) or die();

class DM_URL {
    /**
     * Constructor.
     */
    public function __construct() {
        /**
         * Disable all the URL mapping if viewing through Customizer. This is to
         * ensure maximum functionality by retaining the Admin URL.
         */
        if ( ! empty( $_GET['customize_changeset_uuid'] ) ) {
            return;
        }

        /**
         * This is the earliest possible action we can start to prepare the
         * setup for the mapping logic. This is because the WP->parse_request()
         * method utilises home_url() which means for requests permitted on both
         * the unmapped and mapped domain - like REST API and XMLRPC - will not
         * be properly detected for the rewrite rules.
         */
        add_action( 'muplugins_loaded', array( $this, 'prepare' ), -10 );

        /**
         * Jetpack compatibility. This filter ensures that Jetpack gets the
         * correct domain for the home URL.
         */
        add_action( 'jetpack_sync_home_url', array( $this, 'map' ), 10, 1 );
    }

    /**
     * Map the primary domain on the passed in value if it contains the unmapped
     * URL and the Site has a primary domain.
     *
     * @param  mixed   $value Potentially a value containing the site's unmapped URL.
     * @param  integer $value Site (Blog) ID for the URL which is being mapped.
     * @return string         If unmapped URL is found, then returns the primary URL. Untouched otherwise.
     */
    public function map( $value = '', $blog_id = 0 ) {
        /**
         * Ensure that we are working with a string.
         */
        if ( ! is_string( $value ) ) {
            return $value;
        }

        /**
         * Retrieve the current blog.
         */
        $blog    = get_site( absint( $blog_id ) );
        $primary = DarkMatter_Primary::instance()->get( $blog->blog_id );

        $unmapped = untrailingslashit( $blog->domain . $blog->path );

        /**
         * If there is no primary domain or the unmapped version cannot be found
         * then we return the value as-is.
         */
        if ( empty( $primary ) || false === stripos( $value, $unmapped ) ) {
            return $value;
        }

        $domain = 'http' . ( $primary->is_https ? 's' : '' ) . '://' . $primary->domain;

        return preg_replace( "#https?://{$unmapped}#", $domain, $value );
    }

    /**
     * Setup the actions to handle the URL mappings.
     *
     * @return void
     */
    public function prepare() {
        add_filter( 'the_content', array( $this, 'map' ), 50, 1 );

        /**
         * We only wish to affect `the_content` for Previews and nothing else.
         */
        if ( ! empty( $_GET['preview'] ) || ! empty( $_GET['p'] ) ) {
            return;
        }

        /**
         * Treat the Admin area slightly differently. This is because we do not
         * wish to modify all URLs to the mapped primary domain as this will
         * affect database and cache updates to ensure compatibility if the
         * domain mapping is changed or removed.
         */
        if ( is_admin() || false !== strpos( $_SERVER['REQUEST_URI'], rest_get_url_prefix() )  ) {
            add_action( 'admin_init', array( $this, 'prepare_admin' ) );
            return;
        }

        /**
         * Every thing here is designed to ensure all URLs throughout WordPress
         * is consistent. This is the public serving / theme powered code.
         */
        add_filter( 'home_url', array( $this, 'siteurl' ), -10, 4 );
        add_filter( 'site_url', array( $this, 'siteurl' ), -10, 4 );
        add_filter( 'content_url', array( $this, 'map' ), -10, 1 );

        add_filter( 'script_loader_tag', array( $this, 'map' ), -10, 4 );
        add_filter( 'style_loader_tag', array( $this, 'map' ), -10, 4 );

        add_filter( 'upload_dir', array( $this, 'upload' ), 10, 1 );
    }

    /**
     * Some filters need to be handled later in the process when running in the
     * admin area. This handles the preparation work for mapping URLs for Admni
     * only requests.
     *
     * @return void
     */
    function prepare_admin() {
        /**
         * This is to prevent the Classic Editor's AJAX action for inserting a
         * link from putting the mapped domain in to the database. However, we
         * cannot rely on `is_admin()` as this is always true for calls to the
         * old AJAX. Therefore we check the referer to ensure it's the admin
         * side rather than the front-end.
         */
        if ( wp_doing_ajax()
            &&
                false !== stripos( wp_get_referer(), '/wp-admin/' )
            &&
                ( empty( $_POST['action'] ) || ! in_array( $_POST['action'], array( 'query-attachments', 'sample-permalink', 'upload-attachment' ) ) )
        ) {
            return;
        }

        add_filter( 'home_url', array( $this, 'siteurl' ), -10, 4 );
    }

    /**
     * Handle Home URL and Site URL mappings when and where appropriate.
     *
     * @param  string  $url     The complete site URL including scheme and path.
     * @param  string  $path    Path relative to the site URL. Blank string if no path is specified.
     * @param  string  $scheme  Scheme to give $url. Currently 'http', 'https', 'login', 'login_post', 'admin', 'relative', 'rest', 'rpc', or null.
     * @param  integer $blog_id Site ID, or null for the current site.
     * @return string           Mapped URL unless a specific scheme which should be ignored.
     */
    public function siteurl( $url = '', $path = '', $scheme = null, $blog_id = 0 ) {
        global $wp_customize;

        /**
         * Ensure we are not in Customizer.
         */
        if ( is_a( $wp_customize, 'WP_Customize_Manager' ) ) {
            return $url;
        }

        $valid_schemes = array( 'http', 'https' );

        if ( apply_filters( 'darkmatter_allow_logins', false ) ) {
            $valid_schemes[] = 'login';
        }

        if ( null === $scheme || in_array( $scheme, $valid_schemes ) ) {
            /**
             * Determine if there is any query string paramters present.
             */
            $query = wp_parse_url( $url, PHP_URL_QUERY );

            if ( ! empty( $query ) ) {
                /**
                 * Retrieve and construct an array of the various query strings.
                 */
                parse_str( $query, $parts );

                /**
                 * Determine if the URL we are attempting to map is a Preview
                 * URL, which is to remain on the Admin domain.
                 */
                if ( ! empty( $parts['p'] ) || ! empty( $parts['preview'] ) ) {
                    return $url;
                }
            }

            /**
             * We pass in the potential URL along with the Blog ID. This covers
             * when the `get_home_url()` and `home_url()` are called from within
             * a `switch_blog()` context.
             */
            return $this->map( $url, $blog_id );
        }

        return $url;
    }

    /**
     * Map the primary domain on the passed in value if it contains the unmapped
     * URL and the Site has a primary domain.
     *
     * @param  mixed $value Potentially a value containing the site's unmapped URL.
     * @return mixed        If unmapped URL is found, then returns the primary URL. Untouched otherwise.
     */
    public function unmap( $value = '' ) {
        /**
         * Ensure that we are working with a string.
         */
        if ( ! is_string( $value ) ) {
            return $value;
        }

        /**
         * Retrieve the current blog.
         */
        $blog    = get_site();
        $primary = DarkMatter_Primary::instance()->get();

        $unmapped = 'http' . ( $primary->is_https ? 's' : '' ) . '://' . untrailingslashit( $blog->domain . $blog->path );

        /**
         * If there is no primary domain or the primary domain cannot be found
         * then we return the value as-is.
         */
        if ( empty( $primary ) || false === stripos( $value, $primary->domain ) ) {
            return $value;
        }

        return preg_replace( "#https?://{$primary->domain}#", $unmapped, $value );
    }

    /**
     * Handle the Uploads URL mappings when and where appropriate.
     *
     * @param  array $uploads Array of upload directory data with keys of 'path', 'url', 'subdir, 'basedir', 'baseurl', and 'error'.
     * @return array          Array of upload directory data with the values with URLs mapped as appropriate.
     */
    public function upload( $uploads ) {
        if ( ! empty( $uploads['url'] ) ) {
            $uploads['url'] = $this->map( $uploads['url'] );
        }

        if ( ! empty( $uploads['baseurl'] ) ) {
            $uploads['baseurl'] = $this->map( $uploads['baseurl'] );
        }

        return $uploads;
    }

    /**
     * Return the Singleton Instance of the class.
     *
     * @return void
     */
    public static function instance() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }
}
DM_URL::instance();
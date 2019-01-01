<?php

defined( 'ABSPATH' ) or die();

class DM_URL {
    /**
     * Constructor.
     */
    public function __construct() {
        /**
         * This is the earliest possible action we can start to prepare the
         * setup for the mapping logic. This is because the WP->parse_request()
         * method utilises home_url() which means for requests permitted on both
         * the unmapped and mapped domain - like REST API and XMLRPC - will not
         * be properly detected for the rewrite rules.
         */
        add_action( 'parse_request', array( $this, 'prepare' ) );
    }

    /**
     * Map the primary domain on the passed in value if it contains the unmapped
     * URL and the Site has a primary domain.
     *
     * @param  mixed $value Potentially a value containing the site's unmapped URL.
     * @return mixed        If unmapped URL is found, then returns the primary URL. Untouched otherwise.
     */
    public function map( $value = '' ) {
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
        add_filter( 'home_url', array( $this, 'siteurl' ), -10, 4 );
        add_filter( 'site_url', array( $this, 'siteurl' ), -10, 4 );

        if ( is_admin() ) {
            return;
        }

        add_filter( 'content_url', array( $this, 'map' ), -10, 1 );

        add_filter( 'script_loader_tag', array( $this, 'map' ), -10, 4 );
        add_filter( 'style_loader_tag', array( $this, 'map' ), -10, 4 );

        add_filter( 'the_content', array( $this, 'map' ), 50, 1 );
        add_filter( 'upload_dir', array( $this, 'upload' ), 10, 1 );
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
        if ( null === $scheme || in_array( $scheme, array( 'http', 'https' ) ) ) {
            return $this->map( $url );
        }

        return $url;
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
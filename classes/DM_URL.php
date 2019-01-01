<?php

defined( 'ABSPATH' ) or die();

class DM_URL {
    /**
     * Constructor.
     */
    public function __construct() {}

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
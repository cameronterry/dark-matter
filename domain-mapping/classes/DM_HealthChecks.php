<?php

defined( 'ABSPATH' ) || die;

class DM_HealthChecks {
    /**
     * Constructor.
     */
    public function __construct() {
        add_filter( 'site_status_tests', [ $this, 'add_tests' ] );
    }

    /**
     * Add tests to Site Health relevant to the domain mapping within Dark Matter.
     *
     * @param  array $tests Tests as defined by WordPress as well as plugins and themes.
     * @return array        All tests including domain mapping relevant tests.
     */
    public function add_tests( $tests = [] ) {
        $tests['direct']['darkmatter_domain_mapping_dropin'] = [
            'label' => __( 'Dark Matter - Domain Mapping - Checking Sunrise dropin', 'dark-matter' ),
            'test'  => [ $this, 'test_dropin' ],
        ];

        $tests['direct']['darkmatter_domain_mapping_ssl'] = [
            'label' => __( 'Dark Matter - Domain Mapping - Checking SSL configuration', 'dark-matter' ),
            'test'  => [ $this, 'test_ssl' ],
        ];

        return $tests;
    }

    /**
     * Checks to ensure the dropin - sunrise.php - exists.
     *
     * @return bool True if sunrise.php exists. False otherwise.
     */
    public function dropin_exists() {
        return file_exists( DM_PATH . '/domain-mapping/sunrise.php' );
    }

    /**
     * Checks to ensure the constant `FORCE_SSL_ADMIN` is configured correctly for Dark Matter.
     *
     * @return bool True if `FORCE_SSL_ADMIN` is present and set. False otherwise.
     */
    public function force_ssl_set() {
        return defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN;
    }

    /**
     * Checks the dropin - sunrise.php - to see if it is the correct version.
     *
     * @return bool True if the dropin is the correct version. False otherwise.
     */
    public function is_dropin_latest() {
        $destination = WP_CONTENT_DIR . '/sunrise.php';
        $source      = DM_PATH . '/domain-mapping/sunrise.php';

        return filesize( $destination ) === filesize( $source ) && md5_file( $destination ) === md5_file( $source );
    }

    /**
     * Checks the Sunrise dropin to ensure it is configured correctly and is up-to-date.
     *
     * @return array Test result.
     */
    public function test_dropin() {
        $result = [
            'label'       => __( 'Sunrise dropin is enabled and up-to-date.', 'dark-matter' ),
            'status'      => 'good',
            'badge'       => array(
                'label' => __( 'Domain Mapping', 'dark-matter' ),
                'color' => 'green',
            ),
            'description' => sprintf(
                '<p>%s</p>',
                __( 'Sunrise is the name of the dropin file which maps custom domains to your WordPress sites.', 'dark-matter' )
            ),
            'actions'     => '',
            'test'        => 'darkmatter_domain_mapping_dropin',
        ];

        if ( ! $this->dropin_exists() ) {
            $result['label']          = __( 'Sunrise dropin cannot be found.', 'dark-matter' );
            $result['badge']['color'] = 'red';
            $result['status']         = 'critical';
            $result['description']    = sprintf(
                '<p>%s</p>',
                __( 'Contact your system administrator to add sunrise.php to your wp-content/ folder.', 'dark-matter' )
            );

            return $result;
        }

        if ( ! $this->is_dropin_latest() ) {
            $result['label']          = __( 'Your Sunrise dropin does not match the Dark Matter version.', 'dark-matter' );
            $result['badge']['color'] = 'orange';
            $result['status']         = 'recommended';
            $result['description']    = sprintf(
                '<p>%s</p>',
                __( 'Sunrise dropin is different from the version recommended by Dark Matter. Please update sunrise.php to the version found in Dark Matter plugin folder.', 'dark-matter' )
            );

            return $result;
        }

        return $result;
    }

    /**
     * Checks SSL configuration for compatibility with Dark Matter domain mapping.
     * @return array Test result.
     */
    public function test_ssl() {
        $result = [
            'label'       => __( 'Your SSL configuration is compatible with Dark Matter.', 'dark-matter' ),
            'status'      => 'good',
            'badge'       => array(
                'label' => __( 'Domain Mapping', 'dark-matter' ),
                'color' => 'green',
            ),
            'description' => sprintf(
                '<p>%s</p>',
                __( 'Your admin area is secured by HTTPS and compatible with domain mapping.', 'dark-matter' )
            ),
            'actions'     => sprintf(
                '<p><a href="%s" target="_blank" rel="noopener noreferrer">%s <span class="screen-reader-text">%s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
                /* translators: Documentation explaining HTTPS and why it should be used. */
                esc_url( __( 'https://wordpress.org/support/article/why-should-i-use-https/' ) ),
                __( 'Learn more about why you should use HTTPS' ),
                /* translators: Accessibility text. */
                __( '(opens in a new tab)' )
            ),
            'test'        => 'darkmatter_domain_mapping_ssl',
        ];

        if ( ! $this->force_ssl_set() ) {
            $result['label']          = __( 'WordPress does not redirect admin requests to HTTPS.', 'dark-matter' );
            $result['badge']['color'] = 'red';
            $result['status']         = 'critical';
            $result['description']    = sprintf(
                '<p>%s</p>',
                sprintf(
                    __( 'Please ensure the %1$s constant is present and set to "true" in your wp-config.php file.', 'dark-matter' ),
                    '<code>FORCE_SSL_ADMIN</code>'
                )
            );

            return $result;
        }

        return $result;
    }

    /**
     * Return the Singleton Instance of the class.
     *
     * @return DM_HealthChecks
     */
    public static function instance() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }
}
DM_HealthChecks::instance();
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
                __( 'Sunrise is the name of the dropin file which maps custom domains to your WordPress sites.' )
            ),
            'actions'     => '',
            'test'        => 'darkmatter_domain_mapping_dropin',
        ];

        return $result;
    }

    /**
     * Checks SSL configuration for compatibility with Dark Matter domain mapping.
     * @return array Test result.
     */
    public function test_ssl() {
        $result = [];

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
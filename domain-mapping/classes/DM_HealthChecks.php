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
            'test'  => [ $this, 'check_dropin' ],
        ];

        $tests['direct']['darkmatter_domain_mapping_ssl'] = [
            'label' => __( 'Dark Matter - Domain Mapping - Checking SSL configuration', 'dark-matter' ),
            'test'  => [ $this, 'check_ssl' ],
        ];

        return $tests;
    }

    /**
     * Checks the Sunrise dropin to ensure it is configured correctly and is up-to-date.
     *
     * @return array Test result.
     */
    public function check_dropin() {
        $result = [];

        return $result;
    }

    /**
     * Checks SSL configuration for compatibility with Dark Matter domain mapping.
     * @return array Test result.
     */
    public function check_ssl() {
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
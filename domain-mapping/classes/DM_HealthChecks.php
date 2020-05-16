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
        $tests['direct']['domain_mapping_sunrise'] = [];

        return $tests;
    }
}
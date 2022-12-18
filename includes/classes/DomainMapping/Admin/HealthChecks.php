<?php
/**
 * Integration with WordPress Core's health setup.
 *
 * @since 2.1.0
 *
 * @package DarkMatterPlugin\DomainMapping
 */

namespace DarkMatter\DomainMapping\Admin;

use DarkMatter\DomainMapping\Manager\Primary;

/**
 * Class HealthChecks
 *
 * Previously called `DM_HealthChecks`.
 *
 * @since 2.1.0
 */
class HealthChecks {
	/**
	 * Constructor.
	 *
	 * @since 2.1.0
	 */
	public function __construct() {
		add_filter( 'site_status_tests', [ $this, 'add_tests' ] );
	}

	/**
	 * Add tests to Site Health relevant to the domain mapping within Dark Matter.
	 *
	 * @since 2.1.0
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

		if ( ! is_main_site() ) {
			$tests['direct']['darkmatter_domain_mapping_primary_domain_set'] = [
				'label' => __( 'Dark Matter - Domain Mapping - Checking for primary domain', 'dark-matter' ),
				'test'  => [ $this, 'test_primary_domain_set' ],
			];
		}

		$tests['direct']['darkmatter_domain_mapping_cookie_domain'] = [
			'label' => __( 'Dark Matter - Domain Mapping - Checking for cookie domain settings', 'dark-matter' ),
			'test'  => [ $this, 'test_cookie_domain' ],
		];

		return $tests;
	}

	/**
	 * Ensures that the COOKIE_DOMAIN constant is set by Dark Matter and not set elsewhere (such as wp-config.php).
	 *
	 * @since 2.1.0
	 *
	 * @return bool True if COOKIE_DOMAIN is set by Dark Matter. False otherwise.
	 */
	public function cookie_domain_dm_set() {
		return ( defined( 'DARKMATTER_COOKIE_SET' ) && DARKMATTER_COOKIE_SET );
	}

	/**
	 * Checks to ensure the dropin - sunrise.php - exists.
	 *
	 * @since 2.1.0
	 *
	 * @return bool True if sunrise.php exists. False otherwise.
	 */
	public function dropin_exists() {
		return file_exists( DM_PATH . '/includes/dropins/sunrise.php' );
	}

	/**
	 * Checks to ensure the constant `FORCE_SSL_ADMIN` is configured correctly for Dark Matter.
	 *
	 * @since 2.1.0
	 *
	 * @return bool True if `FORCE_SSL_ADMIN` is present and set. False otherwise.
	 */
	public function force_ssl_set() {
		return defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN;
	}

	/**
	 * Checks the dropin - sunrise.php - to see if it is the correct version.
	 *
	 * @since 2.1.0
	 *
	 * @return bool True if the dropin is the correct version. False otherwise.
	 */
	public function is_dropin_latest() {
		$destination = WP_CONTENT_DIR . '/sunrise.php';
		$source      = DM_PATH . '/includes/dropins/sunrise.php';

		return filesize( $destination ) === filesize( $source ) && md5_file( $destination ) === md5_file( $source );
	}

	/**
	 * Checks the COOKIE_DOMAIN constant to ensure it is compatible with Dark Matter.
	 *
	 * @since 2.1.0
	 *
	 * @return array Test result.
	 */
	public function test_cookie_domain() {
		$result = [
			'label'       => __( 'Dark Matter single-sign on (bringing the admin bar to the public-facing side) is enabled.', 'dark-matter' ),
			'status'      => 'good',
			'badge'       => [
				'label' => __( 'Domain Mapping', 'dark-matter' ),
				'color' => 'green',
			],
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Dark Matter single-sign on is enabled and can load the admin bar when WordPress users are visiting the public-facing side of your site.', 'dark-matter' )
			),
			'actions'     => '',
			'test'        => 'darkmatter_domain_mapping_cookie_domain',
		];

		if ( ! $this->cookie_domain_dm_set() ) {
			$result['label']          = __( 'The cookie domain constant has been set and Dark Matter SSO has been disabled.', 'dark-matter' );
			$result['badge']['color'] = 'red';
			$result['status']         = 'critical';
			$result['description']    = sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: COOKIE_DOMAIN constant */
					__( 'The %1$s constant has been set, likely within your wp-config.php file. Dark Matter single-sign on (SSO) - which uses %1$s - has been disabled to prevent errors.', 'dark-matter' ),
					'<code>COOKIE_DOMAIN</code>'
				)
			);
		}

		return $result;
	}

	/**
	 * Checks the Sunrise dropin to ensure it is configured correctly and is up-to-date.
	 *
	 * @since 2.1.0
	 *
	 * @return array Test result.
	 */
	public function test_dropin() {
		$result = [
			'label'       => __( 'Sunrise dropin is enabled and up-to-date.', 'dark-matter' ),
			'status'      => 'good',
			'badge'       => [
				'label' => __( 'Domain Mapping', 'dark-matter' ),
				'color' => 'green',
			],
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

		if ( ! defined( 'SUNRISE' ) ) {
			$result['label']          = __( 'SUNRISE constant is not setup.', 'dark-matter' );
			$result['badge']['color'] = 'red';
			$result['status']         = 'critical';
			$result['description']    = sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: SUNRISE constant */
					__( 'Please ensure the %1$s constant is present and set to "true" in your wp-config.php file.', 'dark-matter' ),
					'<code>SUNRISE</code>'
				)
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
		}

		return $result;
	}

	/**
	 * Checks the Sunrise dropin to ensure it is configured correctly and is up-to-date.
	 *
	 * @since 2.1.0
	 *
	 * @return array Test result.
	 */
	public function test_primary_domain_set() {
		$result = [
			'label'       => __( 'You have a primary domain.', 'dark-matter' ),
			'status'      => 'good',
			'badge'       => [
				'label' => __( 'Domain Mapping', 'dark-matter' ),
				'color' => 'green',
			],
			'actions'     => '',
			'test'        => 'darkmatter_domain_mapping_primary_domain_set',
		];

		$primary = Primary::instance()->get();

		if ( empty( $primary ) ) {
			$result['label']          = __( 'You have not a set a primary domain.', 'dark-matter' );
			$result['badge']['color'] = 'orange';
			$result['status']         = 'recommended';
			$result['description']    = sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: link to unmapped homepage url */
					__( 'No primary domain is set. Currently this site is can only be visited on the admin domain at; %1$s.', 'dark-matter' ),
					sprintf(
						'<a href="%1$s">%1$s</a>',
						home_url()
					)
				)
			);
		} else {
			$result['description'] = sprintf(
				'<p>%1$s</p>',
				sprintf(
					/* translators: link to mapped homepage url */
					__( 'People can now visit your website at; %1$s.', 'dark-matter' ),
					sprintf(
						'<a href="%1$s">%1$s</a>',
						home_url()
					)
				)
			);
		}

		return $result;
	}

	/**
	 * Checks SSL configuration for compatibility with Dark Matter domain mapping.
	 *
	 * @since 2.1.0
	 *
	 * @return array Test result.
	 */
	public function test_ssl() {
		$result = [
			'label'       => __( 'Your SSL configuration is compatible with Dark Matter.', 'dark-matter' ),
			'status'      => 'good',
			'badge'       => [
				'label' => __( 'Domain Mapping', 'dark-matter' ),
				'color' => 'green',
			],
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
					/* translators: FORCE_SSL_ADMIN constant */
					__( 'Please ensure the %1$s constant is present and set to "true" in your wp-config.php file.', 'dark-matter' ),
					'<code>FORCE_SSL_ADMIN</code>'
				)
			);
		}

		return $result;
	}
}

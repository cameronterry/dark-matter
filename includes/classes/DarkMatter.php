<?php
/**
 * Hello, this is the beginning of the Dark Matter plugin!
 *
 * @since 3.0.0
 *
 * @package DarkMatter
 */

namespace DarkMatter;

use DarkMatter\DomainMapping;
use DarkMatter\Interfaces\Registerable;

/**
 * Class DarkMatter.
 *
 * @since 3.0.0
 */
class DarkMatter {
	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_rest' ] );

		/**
		 * Register and handle Domain Mapping module.
		 */
		$this->register_domainmapping();
	}

	/**
	 * Register a set of classes used by the Dark Matter plugin.
	 *
	 * @since 3.0.0
	 *
	 * @param array $classes String of class names / namespaces to be instantiated.
	 * @return array Array of the resultant objects.
	 */
	private function class_register( $classes = [] ) {
		if ( empty( $classes ) ) {
			return [];
		}


		$objs = [];
		foreach ( $classes as $class ) {
			$obj = new $class();

			if ( $obj instanceof Registerable ) {
				$obj->register();
			}

			if ( $obj instanceof \WP_CLI_Command ) {
				$obj::define();
			}

			if ( $obj instanceof \WP_REST_Controller ) {
				$obj->register_routes();
			}
		}

		return $objs;
	}

	/**
	 * Register the classes for Domain Mapping.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_domainmapping() {
		/**
		 * Domain type constants.
		 */
		define( 'DM_DOMAIN_TYPE_MAIN', 1 );
		define( 'DM_DOMAIN_TYPE_MEDIA', 2 );

		/**
		 * Basic compatibility/configuration for bbPress and WooCommerce.
		 */
		if (
			/**
			 * Detect if WooCommerce is installed.
			 */
			class_exists( 'WooCommerce' )
			||
			/**
			 * Detect if bbPress is installed.
			 */
			class_exists( 'bbPress' )
		) {
			add_filter( 'darkmatter_allow_logins', '__return_true' );
		}

		$domainmapping_classes = [
			DomainMapping\Installer::class,
			DomainMapping\Processor\Mapping::class,
			DomainMapping\Processor\Media::class,
		];

		if (
			( ! defined( 'DARKMATTER_HIDE_UI' ) || ! DARKMATTER_HIDE_UI )
			&& ! is_main_site()
		) {
			$domainmapping_classes[] = DomainMapping\Admin\DomainSettings::class;
		}

		$domainmapping_classes[] = DomainMapping\Admin\HealthChecks::class;

		if ( defined( 'WPSEO_VERSION' ) ) {
			$domainmapping_classes = DomainMapping\Support\Yoast::class;
		}

		$this->class_register( $domainmapping_classes );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$this->class_register(
				[
					DomainMapping\CLI\Domains::class,
					DomainMapping\CLI\Dropin::class,
					DomainMapping\CLI\Restricted::class,
				]
			);
		}
	}

	/**
	 * Register REST routes. This is separate due to the need to be fired on the `rest_api_init` hook.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_rest() {
		$this->class_register(
			[
				DomainMapping\REST\Domains::class,
				DomainMapping\REST\Restricted::class,
			]
		);
	}

	/**
	 * Return the Singleton Instance of the class.
	 *
	 * @since 3.0.0
	 *
	 * @return DarkMatter
	 */
	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}

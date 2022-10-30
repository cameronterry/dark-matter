<?php
/**
 * Hello, this is the beginning of the Dark Matter plugin!
 *
 * @package DarkMatter
 */

namespace DarkMatter;

use DarkMatter\DomainMapping;
use DarkMatter\Interfaces\Registerable;

/**
 * Class DarkMatter.
 */
class DarkMatter {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_rest' ] );

		/**
		 * Register and handle Domain Mapping module.
		 */
		$this->register_domainmapping();

		new DomainMapping\Installer();
	}

	/**
	 * Register a set of classes used by the Dark Matter plugin.
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
	 * @return void
	 */
	public function register_domainmapping() {
		$domainmapping_classes = [
			DomainMapping\Processor\Mapping::class,
			DomainMapping\Processor\Media::class,
		];

		if (
			( ! defined( 'DARKMATTER_HIDE_UI' ) || ! DARKMATTER_HIDE_UI )
			&& ! is_main_site()
		) {
			$domainmapping_classes[] = DomainMapping\Admin\DomainSettings::class;
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

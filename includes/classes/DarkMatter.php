<?php
/**
 * Hello, this is the beginning of the Dark Matter plugin!
 *
 * @package DarkMatter
 */

namespace DarkMatter;

use DarkMatter\AdvancedCache\Admin\Invalidation;
use DarkMatter\Interfaces\Registerable;

/**
 * Class DarkMatter.
 */
class DarkMatter {
	/**
	 * Constructor
	 */
	public function __construct() {
		/**
		 * Only register advanced cache if it is enabled.
		 */
		if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
			$this->register_advancedcache();
		}
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
		}

		return $objs;
	}

	/**
	 * Register the classes for Advanced Cache.
	 *
	 * @return void
	 */
	public function register_advancedcache() {
		$this->class_register(
			[
				Invalidation::class,
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

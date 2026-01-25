<?php
/**
 * Loader for the plugin PHP classes.
 *
 * @package DarkMatterPlugin\
 */

namespace DarkMatterPlugin;

use DarkMatterPlugin\Vendor\Spatie\StructureDiscoverer\Discover;

/**
 * Class Plugin
 */
final class Plugin {

	/**
	 * Instantiated classes by the plugin on this request.
	 *
	 * @var array
	 */
	private $classes = [];

	/**
	 * The class instance.
	 *
	 * @var null|Plugin
	 */
	private static $instance = null;

	/**
	 * Converts a class name into a slug: an array key friendly version, essentially.
	 *
	 * @param string $class_name Class name to convert into a slug.
	 * @return string
	 */
	private function class_name_slug( $class_name ) {
		return sanitize_title( str_replace( '\\', '--', $class_name ) );
	}

	/**
	 * Retrieve the classes to be loaded.
	 *
	 * @param string $implementing String of the class/interface is to be loaded.
	 * @return Discover
	 */
	public function get_classes( $implementing ) {
		$finder = Discover::in( DMP_INC )->implementing( $implementing );

		return $finder->classes();
	}

	/**
	 * Instantiate the supplied classes from the Discoverer.
	 *
	 * @param Discover $classes Classes to be instantiated.
	 * @return void
	 * @throws \ReflectionException
	 */
	public function instantiate( $classes ) {
		foreach ( $classes->get() as $class ) {
			$slug = $this->class_name_slug( $class );

			/**
			 * Only load the class once.
			 */
			if ( ! empty( $this->classes[ $slug ] ) ) {
				continue;
			}

			/**
			 * Make sure we can work with the class.
			 */
			$reflection = new \ReflectionClass( $class );
			if ( ! $reflection->isInstantiable() ) {
				continue;
			}

			$instantiated_class = new $class();
			if ( $instantiated_class instanceof Registerable ) {
				/**
				 * Instantiate the class and determine if it can be registered.
				 *
				 * @var Registerable $instantiated_class
				 */
				if ( $instantiated_class->can_register() ) {
					$this->classes[ $slug ] = $instantiated_class;
					$instantiated_class->register();
				}
			} elseif ( $instantiated_class instanceof Command ) {
				/**
				 * Instantiate the class and determine if it can be registered.
				 *
				 * @var Command $instantiated_class
				 */
				$instantiated_class = new $class();
				if ( $instantiated_class::can_register() ) {
					$this->classes[ $slug ] = $instantiated_class;
					$instantiated_class::register();
				}
			}
		}
	}

	/**
	 * Load the plugin by instantiating the discovered classes.
	 *
	 * @return void
	 */
	public function load() {
		/**
		 * Instantiate the broader plugin classes.
		 */
		$classes = $this->get_classes( Registerable::class );
		$this->instantiate( $classes );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$classes_cli = $this->get_classes( Command::class );
			$this->instantiate( $classes_cli );
		}
	}

	/**
	 * Singleton.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

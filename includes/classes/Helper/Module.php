<?php
/**
 * Helper class for loading the different modules - such as Domain Mapping, Full Page Caching, etc. - of Dark Matter
 * Plugin.
 *
 * @package DarkMatterPlugin
 */

namespace DarkMatter\Helper;

use DarkMatter\Interfaces\Registerable;
use HaydenPierce\ClassFinder\ClassFinder;

/**
 * Class Module
 */
abstract class Module {

	/**
	 * Instantiated classes.
	 *
	 * @var array
	 */
	protected $classes = [];

	/**
	 * Namespace for the classes of the Module to be loaded.
	 *
	 * @var string
	 */
	private $namespace = '';

	/**
	 * Constructor.
	 *
	 * @param string $namespace Namespace of the module, excluding the root namespace.
	 */
	protected function __construct( $namespace ) {
		$this->namespace = sprintf( '\DarkMatter\%s', $namespace );
	}

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
	 * Retrieves all the classes within a specific namespace.
	 *
	 * @return string[] Classes within the namespace.
	 * @throws \Exception
	 */
	private function get_classes() {
		$finder = new ClassFinder();
		$finder::setAppRoot( DM_PATH . '/' );

		return $finder::getClassesInNamespace( $this->namespace, ClassFinder::RECURSIVE_MODE );
	}

	/**
	 * Loads all the classes within the Module namespace.
	 *
	 * @return void
	 * @throws \ReflectionException
	 */
	public function load() {
		foreach ( $this->get_classes() as $class ) {
			/**
			 * Check and ensure we do not double-load a class.
			 */
			$slug = $this->class_name_slug( $class );
			if ( isset( $this->classes[ $slug ] ) ) {
				continue;
			}

			$reflection = new \ReflectionClass( $class );

			/**
			 * Make sure the class is one that can be worked with.
			 */
			if ( ! $reflection->isInstantiable() || ! $reflection->implementsInterface( '\DarkMatter\Interfaces\Registerable' ) ) {
				continue;
			}

			/**
			 * Instantiate the class and determine if we can register it.
			 *
			 * @var Registerable $class
			 */
			$class = new $class();
			if ( $class->can_register() ) {
				$this->classes[ $slug ] = $class;
				$class->register();
			}
		}
	}
}

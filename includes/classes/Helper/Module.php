<?php
/**
 * Helper class for loading the different modules - such as Domain Mapping, Full Page Caching, etc. - of Dark Matter
 * Plugin.
 *
 * @package DarkMatterPlugin
 */

namespace DarkMatter\Helper;

/**
 * Class Module
 */
class Module {

	/**
	 * Instantiated classes.
	 *
	 * @var array
	 */
	private $classes = [];

	/**
	 * Variable storage for the Singleton.
	 *
	 * @var null|Module
	 */
	private static $instance = null;
}

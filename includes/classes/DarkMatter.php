<?php
/**
 * Hello, this is the beginning of the Dark Matter plugin!
 *
 * @package DarkMatter
 */

/**
 * Class DarkMatter.
 */
class DarkMatter {
	/**
	 * Constructor
	 */
	public function __construct() {

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

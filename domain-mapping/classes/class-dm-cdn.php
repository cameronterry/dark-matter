<?php
/**
 * Class DM_CDN
 *
 * @package DarkMatter
 * @since 2.2.0
 */

defined( 'ABSPATH' ) || die;

/**
 * Class DM_CDN
 *
 * @since 2.2.0
 */
class DM_CDN {
	/**
	 * Constructor.
	 *
	 * @since 2.2.0
	 */
	public function __construct() {

	}

	/**
	 * Return the Singleton Instance of the class.
	 *
	 * @since 2.2.0
	 *
	 * @return DM_CDN
	 */
	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}
DM_CDN::instance();

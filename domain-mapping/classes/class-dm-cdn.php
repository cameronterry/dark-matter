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
	 * An array of CDN domains.
	 *
	 * @var array
	 */
	private $allowed_mime_types = array();

	/**
	 * An array of CDN domains.
	 *
	 * @var array
	 */
	private $cdn_domains = array();

	/**
	 * The unmapped domain.
	 *
	 * @var string
	 */
	private $unmapped;

	/**
	 * The primary domain, if available.
	 *
	 * @var bool|DM_Domain
	 */
	private $primary;

	/**
	 * Constructor.
	 *
	 * @since 2.2.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 10 );
	}

	/**
	 * Discontinue if there is no CDN domains to map.
	 *
	 * @return bool
	 */
	private function can_map() {
		return ! empty( $this->cdn_domains );
	}

	/**
	 * Convert WordPress Core's allwoed mime types array, which has keys designed for regex, to straight-forward strings
	 * for the individual extensions as keys on the array.
	 *
	 * For example: turn `image/jpeg` mime type key from `jpg|jpeg|jpe` into three separate key / values on the array.
	 *
	 * @return array All mime types and extensions.
	 */
	private function get_mime_types() {
		$mime_types = get_allowed_mime_types();

		foreach ( $mime_types as $extension => $mime_type ) {
			/**
			 * No divided - regex OR - then skip it.
			 */
			if ( false === stripos( $extension, '|' ) ) {
				continue;
			}

			/**
			 * Get the separate extensions.
			 */
			$extensions = explode( '|', $extension );

			/**
			 * Add to the array.
			 */
			foreach ( $extensions as $ext ) {
				$mime_types[ $ext ] = $mime_type;
			}
		}

		return $mime_types;
	}

	/**
	 * Initialise the CDN setup.
	 *
	 * @param int $site_id Site (Blog) ID, used to retrieve the site details and Primary Domain.
	 */
	public function init( $site_id = 0 ) {
		$blog = get_site( $site_id );

		if ( ! is_a( $blog, 'WP_Site' ) ) {
			return;
		}

		/**
		 * Put together the unmapped domain.
		 */
		$this->unmapped = untrailingslashit( $blog->domain . $blog->path );

		/**
		 * Retrieve the primary domain.
		 */
		$this->primary = DarkMatter_Primary::instance()->get( $site_id );

		/**
		 * Retrieve CDN domains.
		 */
		$this->cdn_domains = DarkMatter_Domains::instance()->get_domains_by_type( DM_DOMAIN_TYPE_CDN, $site_id );
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

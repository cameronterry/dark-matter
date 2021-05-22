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

		add_filter( 'the_content', array( $this, 'map' ), 100, 1 );
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

		/**
		 * Retrieve the allowed file types. This is the mechanism used - in this feature / plugin at least - for knowing
		 * what is an attachment in uploads.
		 */
		$this->allowed_mime_types = $this->get_mime_types();
	}

	/**
	 * Map CDN domains where appropriate.
	 *
	 * @param  string $content Content containing URLs - or a URL - to be adjusted.
	 * @return string
	 */
	public function map( $content = '' ) {
		if ( ! $this->can_map() || empty( $content ) ) {
			return $content;
		}

		$domains = array(
			$this->unmapped,
		);

		if ( ! empty( $this->primary ) ) {
			$domains[] = $this->primary->domain;
		}

		$domains_regex = implode( '|', $domains );

		/**
		 * Find all URLs which are not mapped to a CDN domain, but are either on the primary domain or admin domain (to
		 * avoid confusion with a third party image, like GIPHY), and process them.
		 *
		 * Note on the regular expression: This regex looks a bit odd, and basically it's to find all URLs after the
		 * protocol until it hits a character we do not want, like closing double-quote (") on a tag or whitespace.
		 * Testing different expressions, some more concise, this was the most performant in a small generated sample of
		 * `post_content` with a four-image gallery.
		 *
		 * * https?://(?:mappeddomain1\.test|helloworld\.com)/[^">\s]+ - 1,130 steps (https://regex101.com/r/mrEb9U/1)
		 * * http?s:\/\/(?:.*?)\.(?:jpg|jpeg|gif|png|pdf) - 7,283 steps (https://regex101.com/r/XPkFIA/1)
		 * * ([-a-z0-9_\/:.]+\.(jpg|jpeg|png)) - 11,593 steps (https://regex101.com/r/mGlUIH/1)
		 *
		 * There will no doubt be some pitfalls, but the performance profile of this approach seems to outweigh those
		 * concerns (at the time of writing this).
		 */
		$urls = [];
		preg_match_all( "#https?://(?:{$domains_regex})/[^\"'>\s]+#", $content, $urls );

		/**
		 * Remove any duplicates.
		 */
		$urls = array_unique( $urls );

		/**
		 * No URLs, skip.
		 */
		if ( empty( $urls ) || empty( $urls[0] ) ) {
			return $content;
		}

		$cdn_domains_count = count( $this->cdn_domains ) - 1;

		/**
		 * Loop through all the URLs and replace as required.
		 */
		foreach ( $urls[0] as $url ) {
			$extension = pathinfo( $url, PATHINFO_EXTENSION );

			/**
			 * No extension, then we can ignore it.
			 */
			if ( empty( $extension ) ) {
				continue;
			}

			/**
			 * Check for a valid extension.
			 */
			if ( array_key_exists( strtolower( $extension ), $this->allowed_mime_types ) ) {
				/**
				 * Alternate through the CDN domains if there is more than one.
				 */
				$index = 0;

				if ( $cdn_domains_count > 0 ) {
					$index = wp_rand( 0, $cdn_domains_count );
				}

				$new_url = str_ireplace( $domains, $this->cdn_domains[ $index ]->domain, $url );
				$content = str_ireplace( $url, $new_url, $content );
			}
		}

		return $content;
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

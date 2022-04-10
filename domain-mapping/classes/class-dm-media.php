<?php
/**
 * Class DM_Media
 *
 * @package DarkMatter
 *
 * @since 2.2.0
 */

defined( 'ABSPATH' ) || die;

/**
 * Class DM_Media
 *
 * @since 2.2.0
 */
class DM_Media {
	/**
	 * An array of Media domains.
	 *
	 * @var array
	 */
	private $allowed_mime_types = array();

	/**
	 * An array of Media domains.
	 *
	 * @var array
	 */
	private $media_domains = array();

	/**
	 * Number of Media domains that are available.
	 *
	 * @var int
	 */
	private $media_domains_count = -1;

	/**
	 * Media domains, as strings.
	 *
	 * @var array
	 */
	private $main_domains = array();

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
		add_action( 'rest_api_init', array( $this, 'prepare_rest' ) );

		add_filter( 'the_content', array( $this, 'map' ), 100, 1 );
		add_filter( 'wp_get_attachment_url', array( $this, 'map_url' ), 100, 1 );
		add_filter( 'wp_insert_post_data', array( $this, 'insert_post' ), -10, 1 );
	}

	/**
	 * Discontinue if there is no Media domains to map.
	 *
	 * @return bool
	 *
	 * @since 2.2.0
	 */
	private function can_map() {
		return ! empty( $this->media_domains );
	}

	/**
	 * Convert WordPress Core's allowed mime types array, which has keys designed for regex, to straight-forward strings
	 * for the individual extensions as keys on the array.
	 *
	 * For example: turn `image/jpeg` mime type key from `jpg|jpeg|jpe` into three separate key / values on the array.
	 *
	 * @return array All mime types and extensions.
	 *
	 * @since 2.2.0
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
	 * Initialise the Media setup.
	 *
	 * @param int $site_id Site (Blog) ID, used to retrieve the site details and Primary Domain.
	 * @return void
	 *
	 * @since 2.2.0
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
		 * Retrieve media domains and update how many are available.
		 */
		$this->media_domains = DarkMatter_Domains::instance()->get_domains_by_type( DM_DOMAIN_TYPE_MEDIA, $site_id );

		/**
		 * Zero-based the count of media domains.
		 */
		$this->media_domains_count = count( $this->media_domains ) - 1;

		/**
		 * Generate an array of only strings of the domains. Namely the primary and unmapped.
		 */
		$this->main_domains = array(
			$this->unmapped,
		);

		if ( ! empty( $this->primary ) ) {
			$this->main_domains[] = $this->primary->domain;
		}

		/**
		 * Retrieve the allowed file types. This is the mechanism used - in this feature / plugin at least - for knowing
		 * what is an attachment in uploads.
		 */
		$this->allowed_mime_types = $this->get_mime_types();
	}

	/**
	 * Clean up the `post_content` to restore the data to the original state as if Dark Matter was not present.
	 *
	 * @param  array $data An array of slashed, sanitized, and processed post data.
	 * @return array       Post data, with URLs unmapped.
	 *
	 * @since 2.2.0
	 */
	public function insert_post( $data = [] ) {
		if ( ! empty( $data['post_content'] ) ) {
			$data['post_content'] = $this->unmap( $data['post_content'] );
		}

		return $data;
	}

	/**
	 * Map Media domains where appropriate.
	 *
	 * @param  string $content Content containing URLs - or a URL - to be adjusted.
	 * @return string
	 *
	 * @since 2.2.0
	 */
	public function map( $content = '' ) {
		if ( ! $this->can_map() || empty( $content ) ) {
			return $content;
		}

		$domains_regex = implode( '|', $this->main_domains );

		/**
		 * Find all URLs which are not mapped to a Media domain, but are either on the primary domain or admin domain
		 * (to avoid confusion with a third party image, like GIPHY), and process them.
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
				$content = str_ireplace( $url, $this->map_url( $url ), $content );
			}
		}

		return $content;
	}

	/**
	 * Used to map a URL to a Media domain. Any URL passed into this method **will** be mapped.
	 *
	 * @param  string $url URL to be modified.
	 * @return string      URL with the domain changed to be from a Media domain.
	 *
	 * @since 2.2.0
	 */
	public function map_url( $url = '' ) {
		/**
		 * No Media domains or the URL is blank, then bail.
		 */
		if ( ! $this->can_map() || empty( $url ) ) {
			return $url;
		}

		/**
		 * Check to ensure the URL is on a domain we can map. This is also used to prevent double-mapping occurring.
		 */
		$do_map = false;
		foreach ( $this->main_domains as $main_domain ) {
			if ( false !== stripos( $url, $main_domain ) ) {
				$do_map = true;
				break;
			}
		}

		if ( ! $do_map ) {
			return $url;
		}

		/**
		 * Alternate through the Media domains if there is more than one.
		 */
		$index = 0;

		if ( $this->media_domains_count > 0 ) {
			$index = wp_rand( 0, $this->media_domains_count );
		}

		$url = preg_replace(
			'#://(' . implode( '|', $this->main_domains ) . ')#',
			'://' . untrailingslashit( $this->media_domains[ $index ]->domain ),
			$url
		);

		/**
		 * Ensure the URL is HTTPS.
		 */
		return set_url_scheme( $url, 'https' );
	}

	/**
	 * Apply filters to map / unmap asset domains on REST API.
	 *
	 * @return void
	 *
	 * @since 2.2.0
	 */
	public function prepare_rest() {
		/**
		 * Loop all post types with REST endpoints to fix the mapping for content.raw property.
		 */
		$rest_post_types = get_post_types( array( 'show_in_rest' => true ) );

		foreach ( $rest_post_types as $post_type ) {
			add_filter( "rest_prepare_{$post_type}", array( $this, 'prepare_rest_post_item' ), 10, 1 );
		}
	}

	/**
	 * Ensures the "raw" version of the content, typically used by Gutenberg through it's middleware pre-load / JS
	 * hydrate process, gets handled the same as content (which runs through the `the_content` hook).
	 *
	 * @param  WP_REST_Response $item Individual post / item in the response that is being processed.
	 * @return WP_REST_Response       Post / item with the content.raw, if present, mapped.
	 *
	 * @since 2.2.0
	 */
	public function prepare_rest_post_item( $item = null ) {
		if ( isset( $item->data['content']['raw'] ) ) {
			$item->data['content']['raw'] = $this->map( $item->data['content']['raw'] );
		}

		return $item;
	}

	/**
	 * Used to unmap Media domains.
	 *
	 * @param  string $value Value that may contain Media domains.
	 * @return string        Value with the Media domains removed and replaced with the unmapped domain.
	 *
	 * @since 2.2.0
	 */
	public function unmap( $value = '' ) {
		if ( ! $this->can_map() || empty( $value ) ) {
			return $value;
		}

		/**
		 * Create an array of strings of the Media domains.
		 */
		$media_domains = wp_list_pluck( $this->media_domains, 'domain' );

		/**
		 * Ensure we have domains that are to be unmapped.
		 */
		if ( empty( $media_domains ) ) {
			return $value;
		}

		/**
		 * Replace the Media domains with the unmapped domain.
		 */
		return preg_replace(
			'#://(' . implode( '|', $media_domains ) . ')#',
			'://' . $this->unmapped,
			$value
		);
	}

	/**
	 * Return the Singleton Instance of the class.
	 *
	 * @return DM_Media
	 *
	 * @since 2.2.0
	 */
	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}
DM_Media::instance();

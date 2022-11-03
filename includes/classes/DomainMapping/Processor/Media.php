<?php
/**
 * Handles the mapping of media domains.
 *
 * @since 2.2.0
 *
 * @package DarkMatter\DomainMapping
 */

namespace DarkMatter\DomainMapping\Processor;

use DarkMatter\DomainMapping\Manager\Domain;
use DarkMatter\DomainMapping\Manager\Primary;

/**
 * Class Media
 *
 * Previously called `DM_Media`
 *
 * @since 2.2.0
 */
class Media {
	/**
	 * The ID of the current site.
	 *
	 * @var int
	 */
	private $current_site_id = 0;

	/**
	 * Store the main domains for the request site (i.e. the one before any `switch_to_blog()` calls).
	 *
	 * @var array
	 */
	private $request_main_domains = [];

	/**
	 * Storage of the per site settings.
	 *
	 * @var array
	 */
	private $sites = [];

	/**
	 * Constructor.
	 *
	 * @since 2.2.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 10 );
		add_action( 'rest_api_init', array( $this, 'prepare_rest' ) );
		add_action( 'switch_blog', array( $this, 'switch_blog' ), 10, 1 );

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
		return isset( $this->sites[ $this->current_site_id ] ) && false !== $this->sites[ $this->current_site_id ];
	}

	/**
	 * Get the main domains for a particular site.
	 *
	 * @param integer $site_id Site ID to retrieve the main domains for.
	 * @return array Main domains, essentially the unmapped (admin) domain and the primary (mapped) domain.
	 */
	private function get_main_domains( $site_id = 0 ) {
		/**
		 * Ensure the site is actually a site.
		 */
		$blog = get_site( $site_id );
		if ( ! is_a( $blog, 'WP_Site' ) ) {
			return [];
		}

		$unmapped = untrailingslashit( $blog->domain . $blog->path );

		/**
		 * Put together the main domains.
		 */
		$main_domains = [
			$unmapped,
		];

		$primary = Primary::instance()->get( $site_id );
		if ( ! empty( $primary ) ) {
			$main_domains[] = $primary->domain;
		}

		return $main_domains;
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
	 * @return void
	 *
	 * @since 2.2.0
	 */
	public function init() {
		$this->current_site_id      = get_current_blog_id();
		$this->request_main_domains = $this->get_main_domains( $this->current_site_id );

		$this->prime_site( $this->current_site_id );
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

		$domains_regex = implode( '|', $this->sites[ $this->current_site_id ]['main_domains'] );

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
			if ( array_key_exists( strtolower( $extension ), $this->sites[ $this->current_site_id ]['allowed_mimes'] ) ) {
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

		foreach ( $this->sites[ $this->current_site_id ]['main_domains'] as $main_domain ) {
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

		if ( $this->sites[ $this->current_site_id ]['media_domains_count'] > 1 ) {
			$index = wp_rand( 0, $this->sites[ $this->current_site_id ]['media_domains_count'] );
		}

		$url = preg_replace(
			'#://(' . implode( '|', $this->sites[ $this->current_site_id ]['main_domains'] ) . ')#',
			'://' . untrailingslashit( $this->sites[ $this->current_site_id ]['media_domains'][ $index ]->domain ),
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
	 * @param  \WP_REST_Response $item Individual post / item in the response that is being processed.
	 * @return \WP_REST_Response       Post / item with the content.raw, if present, mapped.
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
	 * Prime the settings needed for media domain mapping a particular website.
	 *
	 * @since 2.3.0
	 *
	 * @param integer $site_id Site ID to prime for media domains.
	 * @return void
	 */
	private function prime_site( $site_id = 0 ) {
		/**
		 * If we have seen this website before, skip doing it again.
		 */
		if ( isset( $this->sites[ $site_id ] ) ) {
			return;
		}

		$main_domains = $this->get_main_domains( $site_id );
		if ( empty( $main_domains ) ) {
			$this->sites[ $site_id ] = false;
			return;
		}

		/**
		 * Ensure we have media domains to use.
		 */
		$media_domains = Domain::instance()->get_domains_by_type( DM_DOMAIN_TYPE_MEDIA, $site_id );
		if ( empty( $media_domains ) ) {
			$this->sites[ $site_id ] = false;
			return;
		}

		/**
		 * Seemingly WordPress' `wp_get_attachment_url()` doesn't seem to fully work as intended for `switch_to_blog()`.
		 * Therefore we must add the requesters' main domains in order for the map / unmap to work, as the media assets
		 * will be served on the requesters' domains rather than the domain of the site it belongs to.
		 */
		$main_domains = array_filter(
			array_merge(
				$main_domains,
				$this->request_main_domains
			)
		);

		$this->sites[ $site_id ] = [
			'allowed_mimes'       => $this->get_mime_types(),
			'main_domains'        => $main_domains,
			'media_domains'       => $media_domains,
			'media_domains_count' => count( $media_domains ),
			/**
			 * The first entry is always the unmapped.
			 */
			'unmapped'            => $main_domains[0],
		];
	}

	/**
	 * Handle the `switch_to_blog()` / `restore_current_blog()` functionality.
	 *
	 * @since 2.3.0
	 *
	 * @param int $site_id Site (Blog) ID, used to retrieve the site details and Primary Domain.
	 * @return void
	 */
	public function switch_blog( $site_id = 0 ) {
		$this->current_site_id = ( ! empty( $site_id ) ? intval( $site_id ) : get_current_blog_id() );
		$this->prime_site( $this->current_site_id );
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
		$media_domains = wp_list_pluck( $this->sites[ $this->current_site_id ]['media_domains'], 'domain' );

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
			'://' . $this->sites[ $this->current_site_id ]['unmapped'],
			$value
		);
	}
}

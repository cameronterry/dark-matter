<?php
/**
 * Class DM_URL
 *
 * @package DarkMatter
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || die();

/**
 * Class DM_URL
 *
 * @since 2.0.0
 */
class DM_URL {
	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		/**
		 * Disable all the URL mapping if viewing through Customizer. This is to
		 * ensure maximum functionality by retaining the Admin URL.
		 */

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['customize_changeset_uuid'] ) ) {
			return;
		}

		/**
		 * Dark Matter will disengage if the website is no longer public or is
		 * archived or deleted.
		 */
		$blog = get_site();

		if ( (int) $blog->public < 0 || '0' !== $blog->archived || '0' !== $blog->deleted ) {
			return;
		}

		/**
		 * This is the earliest possible action we can start to prepare the
		 * setup for the mapping logic. This is because the WP->parse_request()
		 * method utilises home_url() which means for requests permitted on both
		 * the unmapped and mapped domain - like REST API and XMLRPC - will not
		 * be properly detected for the rewrite rules.
		 */
		add_action( 'muplugins_loaded', array( $this, 'prepare' ), -10 );

		/**
		 * Jetpack compatibility. This filter ensures that Jetpack gets the
		 * correct domain for the home URL.
		 */
		add_action( 'jetpack_sync_home_url', array( $this, 'map' ), 10, 1 );
	}

	/**
	 * Handle the mapping of Admin URLs for the public-serving side when viewed
	 * on a primary domain. This is specifically to ensure compatibility with
	 * plugins which utilise the `admin-ajax.php` and `admin-post.php` URLs for
	 * functionality, such as form postbacks and other such functionality on the
	 * public-serving side.
	 *
	 * @since 2.0.0
	 *
	 * @param  string   $url     The complete admin area URL including scheme and path.
	 * @param  string   $path    Path relative to the admin area URL. Blank string if no path is specified.
	 * @param  int|null $blog_id Site ID, or null for the current site.
	 * @return string            URL which is mapped if appropriate, unchanged otherwise.
	 */
	public function adminurl( $url = '', $path = '', $blog_id = 0 ) {
		$filename = basename( $path );

		/**
		 * This will cover a number of requests which are only `/wp-admin/` with
		 * no file in the $path.
		 */
		if ( empty( $filename ) ) {
			return $url;
		}

		$valid_paths = array(
			'admin-ajax.php',
			'admin-post.php',
		);

		if ( in_array( $filename, $valid_paths ) ) {
			return $this->map( $url, $blog_id );
		}

		return $url;
	}

	/**
	 * Checks to ensure that "mapped" domains are considered internal to WordPress and not external.
	 *
	 * @since 2.0.0
	 *
	 * @param  bool   $external Whether HTTP request is external or not.
	 * @param  string $host     Host name of the requested URL.
	 * @return bool             False if the URL is "mapped" domain. The provided $external value otherwise.
	 */
	public function is_external( $external = false, $host = '' ) {
		/**
		 * WordPress defaults to "false". If it is not this value, then this means another hook has modified it. We skip
		 * this to ensure that original logic is not overridden by Dark Matter.
		 */
		if ( $external ) {
			return $external;
		}

		/**
		 * Attempt to find the domain in Dark Matter. If the domain is found, then tell WordPress it is an internal
		 * domain.
		 */
		$db     = DarkMatter_Domains::instance();
		$domain = $db->find( $host );

		if ( is_a( $domain, 'DM_Domain' ) ) {
			return true;
		}

		return $external;
	}

	/**
	 * Determines if the requested domain is mapped using the DOMAIN_MAPPING
	 * constant from sunrise.php.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean True if the domain is the Primary domain. False if the Admin domain.
	 */
	private function is_mapped() {
		return ( defined( 'DOMAIN_MAPPING' ) && DOMAIN_MAPPING );
	}

	/**
	 * Map the primary domain on the passed in value if it contains the unmapped
	 * URL and the Site has a primary domain.
	 *
	 * @since 2.0.0
	 *
	 * @param  mixed   $value   Potentially a value containing the site's unmapped URL.
	 * @param  integer $blog_id Site (Blog) ID for the URL which is being mapped.
	 * @return string           If unmapped URL is found, then returns the primary URL. Untouched otherwise.
	 */
	public function map( $value = '', $blog_id = 0 ) {
		/**
		 * Ensure that we are working with a string.
		 */
		if ( ! is_string( $value ) ) {
			return $value;
		}

		/**
		 * Retrieve the current blog.
		 */
		$blog    = get_site( absint( $blog_id ) );
		$primary = DarkMatter_Primary::instance()->get( $blog->blog_id );

		$unmapped = untrailingslashit( $blog->domain . $blog->path );

		/**
		 * If there is no primary domain or the unmapped version cannot be found
		 * then we return the value as-is.
		 */
		if ( empty( $primary ) || false === stripos( $value, $unmapped ) ) {
			return $value;
		}

		$domain = 'http' . ( $primary->is_https ? 's' : '' ) . '://' . $primary->domain;

		return preg_replace( "#https?://{$unmapped}#", $domain, $value );
	}

	/**
	 * Setup the actions to handle the URL mappings.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function prepare() {
		add_filter( 'the_content', array( $this, 'map' ), 50, 1 );
		add_filter( 'http_request_host_is_external', array( $this, 'is_external' ), 10, 2 );

		/**
		 * We only wish to affect `the_content` for Previews and nothing else.
		 */

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['preview'] ) || ! empty( $_GET['p'] ) ) {
			return;
		}

		/**
		 * Treat the Admin area slightly differently. This is because we do not
		 * wish to modify all URLs to the mapped primary domain as this will
		 * affect database and cache updates to ensure compatibility if the
		 * domain mapping is changed or removed.
		 */
		$request_uri = ( empty( $_SERVER['REQUEST_URI'] ) ? '' : filter_var( $_SERVER['REQUEST_URI'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW ) );

		if ( is_admin() ) {
			add_action( 'init', array( $this, 'prepare_admin' ) );
			return;
		}

		if ( ! $this->is_mapped() && false !== strpos( $request_uri, rest_get_url_prefix() ) ) {
			add_action( 'rest_api_init', array( $this, 'prepare_rest' ) );
			return;
		}

		/**
		 * Every thing here is designed to ensure all URLs throughout WordPress
		 * is consistent. This is the public serving / theme powered code.
		 */
		add_filter( 'admin_url', array( $this, 'adminurl' ), -10, 3 );
		add_filter( 'home_url', array( $this, 'siteurl' ), -10, 4 );
		add_filter( 'site_url', array( $this, 'siteurl' ), -10, 4 );
		add_filter( 'content_url', array( $this, 'map' ), -10, 1 );
		add_filter( 'get_shortlink', array( $this, 'map' ), -10, 4 );

		add_filter( 'script_loader_tag', array( $this, 'map' ), -10, 4 );
		add_filter( 'style_loader_tag', array( $this, 'map' ), -10, 4 );

		add_filter( 'upload_dir', array( $this, 'upload' ), 10, 1 );
	}

	/**
	 * Some filters need to be handled later in the process when running in the
	 * admin area. This handles the preparation work for mapping URLs for Admni
	 * only requests.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function prepare_admin() {
		/**
		 * This is to prevent the Classic Editor's AJAX action for inserting a
		 * link from putting the mapped domain in to the database. However, we
		 * cannot rely on `is_admin()` as this is always true for calls to the
		 * old AJAX. Therefore we check the referer to ensure it's the admin
		 * side rather than the front-end.
		 */

		if ( wp_doing_ajax()
			&&
				false !== stripos( wp_get_referer(), '/wp-admin/' )
			&&
				( empty( $_POST['action'] ) || ! in_array( $_POST['action'], array( 'query-attachments', 'sample-permalink', 'upload-attachment', 'wp-link-ajax' ) ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
		) {
			return;
		}

		add_filter( 'home_url', array( $this, 'siteurl' ), -10, 4 );

		/**
		 * The Preview link in the metabox of Post Publish cannot be handled by the home_url hook. This is because it
		 * uses get_permalink() to retrieve the URL before appending the "preview=true" query string parameter.
		 *
		 * @link https://github.com/WordPress/WordPress/blob/5.2.2/wp-admin/includes/meta-boxes.php#L57 Preview Metabox call to get Preview URL.
		 * @link https://github.com/WordPress/WordPress/blob/5.2.2/wp-includes/link-template.php#L1311-L1312 Query string parameter "preview=true" being added to the URL.
		 */
		add_filter( 'preview_post_link', array( $this, 'unmap' ), 10, 1 );
	}

	/**
	 * Apply filters to map / unmap URLs for the REST API endpoint and for Block Editor support.
	 *
	 * @since 2.1.2
	 *
	 * @return void
	 */
	public function prepare_rest() {
		add_filter( 'home_url', array( $this, 'siteurl' ), -10, 4 );

		add_filter( 'preview_post_link', array( $this, 'unmap' ), 10, 1 );
	}

	/**
	 * Handle Home URL and Site URL mappings when and where appropriate.
	 *
	 * @since 2.0.0
	 *
	 * @param  string  $url     The complete site URL including scheme and path.
	 * @param  string  $path    Path relative to the site URL. Blank string if no path is specified.
	 * @param  string  $scheme  Scheme to give $url. Currently 'http', 'https', 'login', 'login_post', 'admin', 'relative', 'rest', 'rpc', or null.
	 * @param  integer $blog_id Site ID, or null for the current site.
	 * @return string           Mapped URL unless a specific scheme which should be ignored.
	 */
	public function siteurl( $url = '', $path = '', $scheme = null, $blog_id = 0 ) {
		global $wp_customize;

		/**
		 * Ensure we are not in Customizer.
		 */
		if ( is_a( $wp_customize, 'WP_Customize_Manager' ) ) {
			return $url;
		}

		$valid_schemes = array( 'http', 'https' );

		if ( ! is_admin() ) {
			$valid_schemes[] = 'json';
			$valid_schemes[] = 'rest';
		}

		if ( apply_filters( 'darkmatter_allow_logins', false ) ) {
			$valid_schemes[] = 'login';
		}

		if ( null === $scheme || in_array( $scheme, $valid_schemes ) ) {
			/**
			 * Ensure that if the REST API is called on the admin domain that we do not map the `_links` property on
			 * the response. This will ensure that any integration using these sticks to the correct domain.
			 */
			if ( ! $this->is_mapped() && false !== strpos( $url, rest_get_url_prefix() ) ) {
				return $url;
			}

			/**
			 * Determine if there is any query string paramters present.
			 */
			$query = wp_parse_url( $url, PHP_URL_QUERY );

			if ( ! empty( $query ) ) {
				/**
				 * Retrieve and construct an array of the various query strings.
				 */
				parse_str( $query, $parts );

				/**
				 * Determine if the URL we are attempting to map is a Preview
				 * URL, which is to remain on the Admin domain.
				 */
				if ( ! empty( $parts['p'] ) || ! empty( $parts['page_id'] ) || ! empty( $parts['preview'] ) ) {
					return $url;
				}
			}

			/**
			 * We pass in the potential URL along with the Blog ID. This covers
			 * when the `get_home_url()` and `home_url()` are called from within
			 * a `switch_blog()` context.
			 */
			return $this->map( $url, $blog_id );
		}

		return $url;
	}

	/**
	 * Converts a URL from a mapped domain to the admin domian. This will only convert a URL which is the primary
	 * domain.
	 *
	 * @since 2.0.0
	 *
	 * @param  mixed $value Potentially a value containing the site's unmapped URL.
	 * @return mixed        If unmapped URL is found, then returns the primary URL. Untouched otherwise.
	 */
	public function unmap( $value = '' ) {
		/**
		 * Ensure that we are working with a string.
		 */
		if ( ! is_string( $value ) ) {
			return $value;
		}

		/**
		 * Retrieve the current blog.
		 */
		$blog    = get_site();
		$primary = DarkMatter_Primary::instance()->get();

		/**
		 * If there is no primary domain or the primary domain cannot be found
		 * then we return the value as-is.
		 */
		if ( empty( $primary ) || false === stripos( $value, $primary->domain ) ) {
			return $value;
		}

		$unmapped = 'http' . ( $primary->is_https ? 's' : '' ) . '://' . untrailingslashit( $blog->domain . $blog->path );

		return preg_replace( "#https?://{$primary->domain}#", $unmapped, $value );
	}

	/**
	 * Handle the Uploads URL mappings when and where appropriate.
	 *
	 * @since 2.0.0
	 *
	 * @param  array $uploads Array of upload directory data with keys of 'path', 'url', 'subdir, 'basedir', 'baseurl', and 'error'.
	 * @return array          Array of upload directory data with the values with URLs mapped as appropriate.
	 */
	public function upload( $uploads ) {
		if ( ! empty( $uploads['url'] ) ) {
			$uploads['url'] = $this->map( $uploads['url'] );
		}

		if ( ! empty( $uploads['baseurl'] ) ) {
			$uploads['baseurl'] = $this->map( $uploads['baseurl'] );
		}

		return $uploads;
	}

	/**
	 * Return the Singleton Instance of the class.
	 *
	 * @since 2.0.0
	 *
	 * @return DM_URL
	 */
	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}
DM_URL::instance();

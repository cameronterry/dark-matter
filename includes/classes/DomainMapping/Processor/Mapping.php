<?php
/**
 * Handles the mapping of URLs.
 *
 * @package DarkMatter
 * @since 2.0.0
 */

namespace DarkMatter\DomainMapping\Processor;

use DarkMatter\DomainMapping\Helper;
use DarkMatter\DomainMapping\Manager\Domain;
use DarkMatter\DomainMapping\Manager\Primary;
use DarkMatter\Interfaces\Registerable;

/**
 * Class Mapping
 *
 * Previously called `DM_URL`.
 *
 * @since 2.0.0
 */
class Mapping implements Registerable {
	/**
	 * Determine if the current request was mapped in `sunrise.php`.
	 *
	 * @since 2.3.0
	 *
	 * @var bool
	 */
	public static $is_request_mapped = false;

	/**
	 * Register the hooks and actions.
	 *
	 * @since 3.0.0
	 */
	public function register() {
		self::$is_request_mapped = ( defined( 'DOMAIN_MAPPING' ) && DOMAIN_MAPPING );

		/**
		 * In some circumstances, we always want to process the logic regardless of request type, circumstances,
		 * conditions, etc. (like ensuring saved post data is unmapped properly).
		 */
		add_filter( 'http_request_host_is_external', array( $this, 'is_external' ), 10, 2 );
		add_filter( 'wp_insert_post_data', array( $this, 'insert_post' ), -10, 1 );

		/**
		 * Domain Mapping's definition of "is admin" includes the login and register pages.
		 */
		$is_admin = Helper::instance()->is_admin();

		/**
		 * Prevent accidental URL mapping on requests which are not GET requests for the admin area. For example; a POST
		 * request will include the postback for saving a post.
		 *
		 * A good example is the Yoast SEO plugin when detecting the internal links as part of the SEO score. This hooks
		 * on to the `save_post` action and then uses `home_url()` and `get_permalink()` as part of the process. If the
		 * URLs are mapped / unmapped here then it can cause the functionality to fail.
		 *
		 * @link https://github.com/Yoast/wordpress-seo/blob/11.6/admin/links/class-link-content-processor.php#L43-L48 Yoast SEO code reference.
		 */
		if ( $is_admin && ! wp_doing_ajax() && ! empty( $_SERVER['REQUEST_METHOD'] ) && 'GET' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		/**
		 * This will detect the use of the register or login page, which is "admin" by domain mapping standards.
		 */
		if ( $is_admin && ! is_admin() ) {
			return;
		}

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
		if ( ! Helper::instance()->is_public( $blog ) ) {
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
			'admin-ajax.php' => true,
			'admin-post.php' => true,
		);

		if ( array_key_exists( $filename, $valid_paths ) ) {
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
		$db     = Domain::instance();
		$domain = $db->find( $host );

		if ( is_a( $domain, 'DM_Domain' ) ) {
			return true;
		}

		return $external;
	}

	/**
	 * Unmap URLs prior to the post being created / updated in the database. This ensures that unmapped domains are
	 * stored in the database if provided as well as fixing some issues related internal link tracking used by SEO
	 * plugins.
	 *
	 * This can occur due to the "Search" REST endpoint returning mapped domains (as per Gutenberg) and / or admins and
	 * editors copying and pasting links by navigating the public-facing side of the website.
	 *
	 * @param array $data An array of slashed, sanitized, and processed post data.
	 * @return array Post data, with URLs unmapped.
	 */
	public function insert_post( $data = [] ) {
		if ( ! empty( $data['post_content'] ) ) {
			$data['post_content'] = $this->unmap( $data['post_content'] );
		}

		return $data;
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
		/**
		 * Check to see if this was called within a `switch_to_blog()` context.
		 *
		 * If we are and the request was originally mapped to a primary domain, then we check to ensure the blog within
		 * the context can be mapped (i.e. it has an active primary domain) and if so, we say the request is mapped.
		 */
		global $switched;
		if ( $switched && self::$is_request_mapped ) {
			$primary = Primary::instance()->get();

			/**
			 * If there is no primary or if it is inactive, then the site is not mapped.
			 */
			if ( false === $primary || ! $primary->active ) {
				return false;
			}

			return true;
		}

		return self::$is_request_mapped;
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
		return Helper::instance()->map( $value, $blog_id );
	}

	/**
	 * Setup the actions to handle the URL mappings.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function prepare() {
		/**
		 * We attach twice to `the_content` filter.
		 *
		 * The first one with a high priority (5) is to ensure that URL processing logic - i.e. oEmbeds for other WP
		 * posts - is working with the mapped domain. This is needed to ensure calls to `url_to_postid()` work as
		 * intended as they compared URL hosts.
		 *
		 * The second one, with a low priority (50), is a fail-safe / sweep up. Other logic such as Block Editor process
		 * on a later priority (9 at the time of writing this) to ensure it is correct prior to other plugins possibly
		 * manipulating `post_content`, usually on the "normal" priority (10). For domain mapping, we want to ensure we
		 * catch every thing after WordPress core and any plugins, so we run later in the process to achieve that.
		 */
		add_filter( 'the_content', array( $this, 'map' ), 5, 1 );

		/**
		 * Please note: the `$this->map()` method will check for unmapped URLs before committing to an regex replace. So
		 * most of the time, this will go "nothing to do here". And the rest of time, catch any edge cases that produce
		 * unmapped URLs.
		 */
		add_filter( 'the_content', array( $this, 'map' ), 50, 1 );

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

		/**
		 * This is called for all requests as it is possible for the REST API to be called and process without a cURL
		 * (or equivalent) being used. REST API endpoints can be called internally through `rest_do_request()`, which
		 * bypass the whole need for a request / latency. To ensure that the REST endpoints behave universally, this
		 * action is always called here and not just when the request is called (as in previous versions of Dark
		 * Matter).
		 */
		add_action( 'rest_api_init', array( $this, 'prepare_rest' ) );

		/**
		 * We have to stop here for the REST API as the later filters and hooks can cause the REST API endpoints to 404
		 * if added. So if it is a REST request specifically, then we stop here.
		 */
		if ( false !== strpos( $request_uri, rest_get_url_prefix() ) ) {
			return;
		}

		if ( is_admin() ) {
			add_action( 'init', array( $this, 'prepare_admin' ) );
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
		add_filter( 'home_url', array( $this, 'siteurl' ), -10, 4 );

		/**
		 * The Preview link in the metabox of Post Publish cannot be handled by the home_url hook. This is because it
		 * uses get_permalink() to retrieve the URL before appending the "preview=true" query string parameter.
		 *
		 * @link https://github.com/WordPress/WordPress/blob/5.2.2/wp-admin/includes/meta-boxes.php#L57 Preview Metabox call to get Preview URL.
		 * @link https://github.com/WordPress/WordPress/blob/5.2.2/wp-includes/link-template.php#L1311-L1312 Query string parameter "preview=true" being added to the URL.
		 */
		add_filter( 'preview_post_link', array( $this, 'unmap' ), 10, 1 );

		/**
		 * To prepare the Classic Editor, we need to attach to a very late hook to ensure that `get_current_screen()` is
		 * available and returns something useful.
		 */
		add_action( 'edit_form_top', array( $this, 'prepare_classic_editor' ) );
	}

	/**
	 * Ensures that the Classic Editor is prepared appropriately and the unmapped URLs are mapped prior to loading. This
	 * is needed for compatibility with some SEO plugins such as Yoast.
	 */
	public function prepare_classic_editor() {
		$screen = get_current_screen();

		if ( is_a( $screen, 'WP_Screen' ) && 'post' === $screen->base && 'edit' === $screen->parent_base ) {
			add_filter( 'the_editor_content', array( $this, 'map' ), 10, 1 );
		}
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

		/**
		 * Loop all post types with REST endpoints to fix the mapping for content.raw property.
		 */
		$rest_post_types = get_post_types( [ 'show_in_rest' => true ] );

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
	 */
	public function prepare_rest_post_item( $item = null ) {
		if ( isset( $item->data['content']['raw'] ) ) {
			$item->data['content']['raw'] = $this->map( $item->data['content']['raw'] );
		}

		return $item;
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

		$valid_schemes = array(
			'http'  => true,
			'https' => true,
		);

		if ( ! is_admin() ) {
			$valid_schemes['json'] = true;
			$valid_schemes['rest'] = true;
		}

		if ( apply_filters( 'darkmatter_allow_logins', false ) ) {
			$valid_schemes[] = 'login';
		}

		if ( null === $scheme || array_key_exists( $scheme, $valid_schemes ) ) {
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
		return Helper::instance()->unmap( $value );
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
}

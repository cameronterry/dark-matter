<?php
/**
 * Data object for Request.
 *
 * @package DarkMatter\AdvancedCache
 */
namespace DarkMatter\AdvancedCache\Processor;

use DarkMatter\AdvancedCache\Data\CacheEntry;

/**
 * Class Request
 *
 * @since 3.0.0
 */
class Visitor {
	/**
	 * Arguments provided by Query Strings.
	 *
	 * @var array
	 */
	public $args = [];

	/**
	 * Cookies.
	 *
	 * @var array
	 */
	public $cookies = [];

	/**
	 * The request data, usually provided by `$_SERVER`.
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 * Domain name.
	 *
	 * @var string
	 */
	public $domain = '';

	/**
	 * Full URL.
	 *
	 * @var string
	 */
	public $full_url = '';

	/**
	 * Is an administrator or an editor logged in?
	 *
	 * @var bool
	 */
	public $is_wp_logged_in = false;

	/**
	 * HTTP Method.
	 *
	 * @var string
	 */
	public $method = 'GET';

	/**
	 * Path, the part of the URL after the domain.
	 *
	 * @var string
	 */
	public $path = '';

	/**
	 * Protocol, usually HTTP / HTTPS.
	 *
	 * @var string
	 */
	public $protocol = '';

	/**
	 * IP address of the visitor. Can be `false` if invalid or malformed.
	 *
	 * @var bool|string
	 */
	public $visitor_ip = '';

	/**
	 * User agent of the visitor.
	 *
	 * @var string
	 */
	public $useragent = '';

	/**
	 * Constructor
	 */
	public function __construct( $data = [], $cookies = [] ) {
		/**
		 * Translate applicable and useful request data to properties.
		 *
		 * @link https://www.php.net/manual/en/reserved.variables.server.php
		 */
		if ( ! empty( $data ) ) {
			$this->data = $data;

			$this->visitor_ip = $this->get_visitor_ip();
			$this->useragent  = $this->data['HTTP_USER_AGENT'];

			$this->set_uri_data();
		}

		/**
		 * If the cookies are supplied, then store them.
		 */
		if ( ! empty( $cookies ) ) {
			$this->cookies = $cookies;
			$this->set_cookie_data();
		}
	}

	/**
	 * Retrieve a Cache Entry for the current request.
	 *
	 * @param string $variant_key Variant key.
	 * @return CacheEntry
	 */
	public function cache_get( $variant_key = '' ) {
		$entry = new CacheEntry( $this->full_url, $variant_key );
		return $entry;
	}

	/**
	 * Determine if the request is can be cached.
	 *
	 * @return bool True that request can be cached. False otherwise.
	 */
	public function is_cacheable() {
		/**
		 * Only cache GET and HEAD requests.
		 */
		if ( ! array_key_exists( $this->method, [ 'GET' => true, 'HEAD' => true ] ) || ! empty( $_POST ) ) {
			return false;
		}

		/**
		 * Ensure we do not cache any requests that utilise Basic Authentication.
		 */
		if ( ! empty( $this->get_data_by_key( 'HTTP_AUTHORIZATION' ) ) || ! empty( $this->get_data_by_key( 'PHP_AUTH_USER' ) ) ) {
			return false;
		}

		/**
		 * Certain requests, such as XMLRPC or WP Cron, are not part of the full page caching.
		 */
		$nocache_scripts = [
			'wp-app.php'  => true, // Atom Publishing protocol.
			'wp-cron.php' => true, // Default WordPress Cron system if not disabled / offloaded.
			'xmlrpc.php'  => true, // XML-RPC, which is usually authenticated or handled over POST.
		];
		if ( array_key_exists( strtolower( basename( $this->get_data_by_key( 'SCRIPT_FILENAME' ) ) ), $nocache_scripts ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieve a specific item from the key.
	 *
	 * @param string $key Data key to search for.
	 * @return string Value of the data item. Will return a blank string ('') if not found.
	 */
	public function get_data_by_key( $key = '' ) {
		return $this->data[ $key ] ?? '';
	}

	/**
	 * Retrieves the visitor's IP address, which can be in a number of headers depending on the setup.
	 *
	 * @return false|bool
	 */
	private function get_visitor_ip() {
		/**
		 * If the property is populated, that means we have already processed it.
		 */
		if ( ! empty( $this->visitor_ip ) || false === $this->visitor_ip ) {
			return $this->visitor_ip;
		}

		$ip = '';

		if ( ! empty( $this->data['HTTP_CF_CONNECTING_IP'] ) ) {
			/**
			 * Add support for Cloudflare.
			 */
			$ip = $this->data['HTTP_CF_CONNECTING_IP'];
		}
		else if ( ! empty( $this->data['HTTP_CLIENT_IP'] ) ) {
			$ip = $this->data['HTTP_CLIENT_IP'];
		}
		else if ( ! empty( $this->data['HTTP_X_REAL_IP'] ) ) {
			/**
			 * Check for the IP from an atypical header from a reverse proxy.
			 */
			$ip = $this->data['HTTP_X_REAL_IP'];
		}
		else if ( ! empty( $this->data['HTTP_X_FORWARDED_FOR'] ) ) {
			/**
			 * Check for the IP from another atypical header from a reverse proxy.
			 */
			$ip = $this->data['HTTP_X_FORWARDED_FOR'];
		}
		else if ( ! empty( $this->data['REMOTE_ADDR'] ) ) {
			$ip = $this->data['REMOTE_ADDR'];
		}

		return filter_var( $ip, FILTER_VALIDATE_IP );
	}

	/**
	 * Handle specific data which is cookie related, such as whether administrators and editors are logged in / etc.
	 *
	 * @return void
	 */
	private function set_cookie_data() {
		foreach ( $this->cookies as $cookie => $value ) {
			if (
				'wp' === substr( $cookie, 0, 2 )
				||
				'wordpress' === substr( $cookie, 0, 9 )
				||
				'comment_author' === substr( $cookie, 0, 14 )
			) {
				$this->is_wp_logged_in = true;
				return;
			}
		}
	}

	/**
	 * Set the request data properties.
	 *
	 * @return void
	 */
	private function set_uri_data() {
		$protocol = 'http://';
		if ( isset( $this->data['HTTPS'] ) ) {
			if ( 'on' == strtolower( $this->get_data_by_key( 'HTTPS' ) ) || '1' == $this->get_data_by_key( 'HTTPS' ) ) {
				$protocol = 'https://';
			}
		} elseif ( '443' == $this->get_data_by_key( 'SERVER_PORT' ) ) {
			$protocol = 'https://';
		}

		$this->protocol = $protocol;

		/**
		 * Retrieve the domain name and removing any superfluous chars.
		 */
		$host = rtrim( trim( $this->get_data_by_key( 'HTTP_HOST' ) ), '/' );
		$this->domain = $host;

		/**
		 * Break up the Request URI into two portions; 1) the path, the bit before the query string, and 2) the query
		 * string in a more usable array.
		 */
		$path       = ltrim( trim( $this->get_data_by_key( 'REQUEST_URI' ) ), '/' );
		$this->path = strtok( $path, '?' );

		/**
		 * Convert the query string into an array.
		 */
		$querystring = str_replace( $this->path . '?', '', $path );
		parse_str( $querystring, $this->args );

		/**
		 * Setup the full URL.
		 */
		$this->full_url = $protocol . $host . '/' . $this->path;

		/**
		 * Get the HTTP Method.
		 */
		$this->method = strtoupper( $this->get_data_by_key( 'REQUEST_METHOD' ) );
	}
}

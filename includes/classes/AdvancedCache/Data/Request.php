<?php
/**
 * Data object for Request.
 *
 * @package DarkMatter\AdvancedCache
 */
namespace DarkMatter\AdvancedCache\Data;

/**
 * Class Request
 *
 * @since 3.0.0
 */
class Request {
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
	public function __construct() {
		/**
		 * Translate applicable and useful request data to properties.
		 *
		 * @link https://www.php.net/manual/en/reserved.variables.server.php
		 */
		if ( ! empty( $_SERVER ) ) {
			$this->visitor_ip = $this->get_visitor_ip();
			$this->useragent  = $_SERVER['HTTP_USER_AGENT'];

			$this->set_request_data();
		}
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
		if ( ! empty( $_SERVER['HTTP_AUTHORIZATION'] ) || ! empty( $_SERVER['PHP_AUTH_USER'] ) ) {
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
		if ( array_key_exists( strtolower( basename( $_SERVER['SCRIPT_FILENAME'] ) ), $nocache_scripts ) ) {
			return false;
		}

		return true;
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

		if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			/**
			 * Add support for Cloudflare.
			 */
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		}
		else if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		else if ( ! empty( $_SERVER['HTTP_X_REAL_IP'] ) ) {
			/**
			 * Check for the IP from an atypical header from a reverse proxy.
			 */
			$ip = $_SERVER['HTTP_X_REAL_IP'];
		}
		else if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			/**
			 * Check for the IP from another atypical header from a reverse proxy.
			 */
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return filter_var( $ip, FILTER_VALIDATE_IP );
	}

	/**
	 * Set the request data properties.
	 *
	 * @return void
	 */
	private function set_request_data() {
		$protocol = 'http://';
		if ( isset( $_SERVER['HTTPS'] ) ) {
			if ( 'on' == strtolower( $_SERVER['HTTPS'] ) || '1' == $_SERVER['HTTPS'] ) {
				$protocol = 'https://';
			}
		} elseif ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
			$protocol = 'https://';
		}

		$host = rtrim( trim( $_SERVER['HTTP_HOST'] ), '/' );
		$path = ltrim( trim( $_SERVER['REQUEST_URI'] ), '/' );

		$this->domain   = $host;
		$this->path     = $path;
		$this->protocol = $protocol;

		/**
		 * Setup the full URL.
		 */
		$this->full_url = $protocol . $host . '/' . $path;

		/**
		 * Get the HTTP Method.
		 */
		$this->method = strtoupper( $_SERVER['REQUEST_METHOD'] );
	}
}

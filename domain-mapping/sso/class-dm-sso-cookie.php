<?php
/**
 * There are three parts to the Domain Mapping single-sign on;
 *
 * 1) the JavaScript include on the header for each blog accessed through domain
 *    mapping.
 * 2) Create an authentication token and then redirect back to the mapped
 *    domain.
 * 3) Validate the token and then login the user with that token.
 *
 * The authentication token has to be created on the WordPress Network domian
 * and **NOT** the mapped domain. This is because the process uses Third Party
 * cookies and therefore the user is logged in on the WordPress Network domain.
 * Therefore an authentication token can only be generated by the WordPress
 * Network domain.
 *
 * The token is then provided in a URL request to the mapped domain blog and
 * then the token is used to create an session cookie to login the user.
 *
 * @package DarkMatter
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || die();

// phpcs:disable WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___SERVER__HTTP_USER_AGENT__

/**
 * Class DM_SSO_Cookie
 *
 * @since 2.0.0
 */
class DM_SSO_Cookie {
	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_action( 'admin_post_dark_matter_dmsso', array( $this, 'login_token' ) );
		add_action( 'admin_post_nopriv_dark_matter_dmsso', array( $this, 'login_token' ) );

		add_action( 'admin_post_dark_matter_dmcheck', array( $this, 'logout_token' ) );
		add_action( 'admin_post_nopriv_dark_matter_dmcheck', array( $this, 'logout_token' ) );

		if ( ! $this->is_admin_domain() ) {
			add_action( 'wp_head', array( $this, 'head_script' ) );
			add_action( 'plugins_loaded', array( $this, 'validate_token' ) );
		}
	}

	/**
	 * Creates a nonce that isn't linked to a user, like the APIs in WordPress Core, but functions in a similar fashion.
	 *
	 * @since 2.0.4
	 *
	 * @param  string $action Value which creates the unique nonce.
	 * @return string         Nonce token for use.
	 */
	private function create_shared_nonce( $action = '' ) {
		$i = wp_nonce_tick();
		return substr( wp_hash( $i . '|' . $action, 'nonce' ), -12, 10 );
	}

	/**
	 * Verify a shared nonce.
	 *
	 * @since 2.0.4
	 *
	 * @see create_shared_nonce()
	 *
	 * @param  string $nonce  Nonce that was used and requires verification.
	 * @param  string $action Value which provides the nonce uniqueness.
	 * @return bool|int         An integer if the nonce check passed, 1 for 0-12 hours ago and 2 for 12-24 hours ago. False otherwise.
	 */
	private function verify_shared_nonce( $nonce = '', $action = '' ) {
		if ( empty( $nonce ) ) {
			return false;
		}

		$i = wp_nonce_tick();

		/**
		 * Nonce generated 0-12 hours ago
		 */
		$expected = substr( wp_hash( $i . '|' . $action, 'nonce' ), -12, 10 );
		if ( hash_equals( $expected, $nonce ) ) {
			return 1;
		}

		/**
		 * Nonce generated 12-24 hours ago
		 */
		$expected = substr( wp_hash( ( $i - 1 ) . '|' . $action, 'nonce' ), -12, 10 );
		if ( hash_equals( $expected, $nonce ) ) {
			return 2;
		}

		/**
		 * Invalid.
		 */
		return false;
	}

	/**
	 * Determines if the current request is on the Admin Domain.
	 *
	 * @since 2.0.0
	 *
	 * @return bool True if current request is on the admin domain. False otherwise.
	 */
	private function is_admin_domain() {
		$network = get_network();

		$http_host = ( empty( $_SERVER['HTTP_HOST'] ) ? '' : wp_strip_all_tags( wp_unslash( $_SERVER['HTTP_HOST'] ) ) );

		if ( ! empty( $network ) && false === stripos( $network->domain, $http_host ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Create the JavaScript output include for logging a user in to the admin
	 * when on the Mapped domain. This is ultimately what makes the Admin Bar
	 * appear.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function login_token() {
		header( 'Content-Type: text/javascript' );

		$this->nocache_headers();

		/**
		 * Ensure that the JavaScript is never empty.
		 */
		echo '// dm_sso' . PHP_EOL;

		if ( is_user_logged_in() ) {
			$referer = ( empty( $_SERVER['HTTP_REFERER'] ) ? '' : wp_strip_all_tags( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) );

			$action = sprintf(
				'darkmatter-sso|%1$s|%2$s',
				$referer,
				md5( empty( $_SERVER['HTTP_USER_AGENT'] ) ? '' : wp_strip_all_tags( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) )
			);

			/**
			 * Construct an authentication token which is passed back along with an
			 * action flag to tell the front end to
			 */
			$url = add_query_arg(
				array(
					'__dm_action' => 'authorise',
					'auth'        => wp_generate_auth_cookie( get_current_user_id(), time() + ( 2 * MINUTE_IN_SECONDS ) ),
					'nonce'       => $this->create_shared_nonce( $action ),
				),
				$referer
			);

			printf( 'window.location.replace( "%1$s" );', esc_url_raw( $url ) );
		}

		die();
	}

	/**
	 * Create the JavaScript output for handling the logout functionality.
	 * Without this, users can end up in a state where they are logged out of
	 * the Admin domain but remain perpetually logged in to the Mapped domains.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function logout_token() {
		header( 'Content-Type: text/javascript' );

		$this->nocache_headers();

		/**
		 * Ensure that the JavaScript is never empty.
		 */
		echo '// dm_sso' . PHP_EOL;

		if ( false === is_user_logged_in() ) {
			$referer = ( empty( $_SERVER['HTTP_REFERER'] ) ? '' : wp_strip_all_tags( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) );

			$url = add_query_arg(
				array(
					'__dm_action' => 'logout',
				),
				$referer
			);
			printf( 'window.location.replace( "%1$s" );', esc_url_raw( $url ) );
		}

		die();
	}

	/**
	 * Adds the <script> tag which references the admin action(s) for handling
	 * cross-domain login and logout.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function head_script() {
		if ( is_main_site() || $this->is_admin_domain() ) {
			return;
		}

		/**
		 * Determine if the mapped domain is HTTPS and if so, ensure that the
		 * Admin domain is also HTTPS. If it isn't, then we cannot do the third
		 * party cookie authentication due to the differing protocols.
		 */
		if ( ( ! defined( 'FORCE_SSL_ADMIN' ) || ! FORCE_SSL_ADMIN ) && is_ssl() ) {
			return;
		}

		$script_url = add_query_arg(
			[
				'action' => 'dark_matter_' . ( false === is_user_logged_in() ? 'dmsso' : 'dmcheck' ),
			],
			network_site_url( '/wp-admin/admin-post.php' )
		);

		/**
		 * Check to see if the user is logged in to the current website on the mapped
		 * domain. We then check the setting "Allow Logins?" to see if it is enabled.
		 * In this scenario, the administrator has decided to let users log in only to
		 * the map domain in some scenarios; likely utilising a Membership-like or
		 * WooCommerce style plugin.
		 */
		if ( is_user_logged_in() && apply_filters( 'darkmatter_allow_logins', false ) ) {
			$user = wp_get_current_user();

			/**
			 * Finally we check the user role to see if the user can edit content and
			 * apply the default functionality for Contributor's and above. The logic
			 * is like this because any one with Administrative or Content curation
			 * ability will have access to the /wp-admin/ area which is on the admin
			 * domain. Therefore ... users will need to login through the admin first.
			 */
			if ( is_a( $user, 'WP_User' ) && false === current_user_can( 'edit_posts' ) ) {
				return;
			}
		}
		?>
		<script type="text/javascript">
			( function () {
				var s = document.createElement("script");
				s.type = "text/javascript";
				s.src = "<?php echo( esc_url( $script_url ) ); ?>&" + ( new Date() ).getTime();
				document.head.appendChild( s );
			} )();
		</script>
		<?php
	}

	/**
	 * Sets the relevant no cache headers using the definition from WordPress Core.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function nocache_headers() {
		if ( headers_sent() ) {
			return;
		}

		/**
		 * Set the headers to prevent caching of the JavaScript include.
		 */
		$nocache_headers = wp_get_nocache_headers();

		foreach ( $nocache_headers as $header_name => $header_value ) {
			header( "{$header_name}: {$header_value}" );
		}
	}

	/**
	 * Handle the validation of the login token and logging in of a user. Also
	 * handle the logout if that action is provided.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function validate_token() {
		$dm_action = ( empty( $_GET['__dm_action'] ) ? '' : wp_strip_all_tags( wp_unslash( $_GET['__dm_action'] ) ) );

		/**
		 * Ensure that URLs with the __dm_action query string are not cached by browsers.
		 */
		if ( ! empty( $dm_action ) ) {
			$this->nocache_headers();
		}

		/**
		 * If the validation token is provided on the admin domain, rather than the primary / mapped domain, then just
		 * ignore it and end processing.
		 */
		if ( $this->is_admin_domain() ) {
			return;
		}

		/**
		 * First check to see if the authorise action is provided in the URL.
		 */
		if ( 'authorise' === $dm_action ) {
			/**
			 * Validate the token provided in the URL.
			 */
			$user_id = wp_validate_auth_cookie( wp_strip_all_tags( wp_unslash( $_GET['auth'] ) ), 'auth' );
			$nonce   = wp_strip_all_tags( wp_unslash( $_GET['nonce'] ) );

			$action = sprintf(
				'darkmatter-sso|%1$s|%2$s',
				( empty( $_SERVER['HTTP_REFERER'] ) ? '' : wp_strip_all_tags( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) ),
				md5( empty( $_SERVER['HTTP_USER_AGENT'] ) ? '' : wp_strip_all_tags( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) )
			);

			/**
			 * Check if the validate token worked and we have a User ID. It will
			 * display an error message or login the User if all works out well.
			 */
			if ( false === $user_id || ! $this->verify_shared_nonce( $nonce, $action ) ) {
				wp_die( 'Oops! Something went wrong with logging in.' );
			} else {
				/**
				 * Create the Login session cookie and redirect the user to the
				 * current page with the URL querystrings for Domain Mapping SSO
				 * removed.
				 */
				wp_set_auth_cookie( $user_id );

                // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				wp_redirect( esc_url( remove_query_arg( array( '__dm_action', 'auth', 'nonce' ) ) ), 302, 'Dark-Matter' );
				die();
			}
		} elseif ( 'logout' === $dm_action ) {
			wp_logout();

            // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			wp_redirect( esc_url( remove_query_arg( array( '__dm_action' ) ) ), 302, 'Dark-Matter' );

			die();
		}
	}

	/**
	 * Return the Singleton Instance of the class.
	 *
	 * @since 2.0.0
	 *
	 * @return DM_SSO_Cookie
	 */
	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}

DM_SSO_Cookie::instance();

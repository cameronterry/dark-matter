<?php
/**
 * Class DM_UI
 *
 * @package DM_UI
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || die;

/**
 * Class DM_UI
 *
 * @since 2.0.0
 */
class DM_UI {
	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		/**
		 * The root website cannot be mapped.
		 */
		if ( is_main_site() ) {
			return;
		}

		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}

	/**
	 * Initialise the admin menu and prep the hooks for the CSS and JavaScript
	 * includes.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function admin_menu() {
		$hook_suffix = add_options_page(
			__( 'Domain Mappings', 'dark-matter' ),
			__( 'Domains', 'dark-matter' ),
			$this->get_permission(),
			'domains',
			array(
				$this,
				'page',
			)
		);

		add_action( 'load-' . $hook_suffix, array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueue assets for the Admin Page.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function enqueue() {
		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' );

		wp_register_script(
			'dark-matter-domains',
			DM_PLUGIN_URL . 'domain-mapping/build/domain-mapping' . $min . '.js',
			[ 'wp-i18n' ],
			DM_VERSION,
			true
		);

		wp_localize_script(
			'dark-matter-domains',
			'dmSettings',
			array(
				'rest_root' => get_rest_url(),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
			)
		);

		wp_enqueue_script( 'dark-matter-domains' );

		wp_enqueue_style( 'dark-matter-domains', DM_PLUGIN_URL . 'domain-mapping/build/domain-mapping-style' . $min . '.css', [], DM_VERSION );
	}

	/**
	 * Retrieve the capability that is required for using the admin page.
	 *
	 * @since 2.1.2
	 *
	 * @return string Capability that must be met to use the Admin page.
	 */
	public function get_permission() {
		return apply_filters( 'dark_matter_domain_permission', 'upgrade_network' );
	}

	/**
	 * Very basic HTML output for the
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function page() {
		if ( current_user_can( $this->get_permission() ) ) {
			wp_die( __( 'You do not have permission to manage domains.', 'dark-matter' ) );
		}
		?>
		<div id="root"></div>
		<?php
	}
}

new DM_UI();

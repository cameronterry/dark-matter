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
		$assets = wp_json_file_decode( DM_PATH . 'dist/assets.json', [ 'associative' => true ] );
		foreach ( $assets as $asset ) {
			if ( 'css' === $asset['type'] ) {
				wp_enqueue_style(
					$asset['id'],
					sprintf(
						'%sdist/%s',
						DM_PLUGIN_URL,
						$asset['filename'],
					),
					[],
					$asset['version']
				);
			} elseif ( 'javascript' === $asset['type'] ) {
				wp_enqueue_script(
					$asset['id'],
					sprintf(
						'%sdist/%s',
						DM_PLUGIN_URL,
						$asset['filename'],
					),
					$asset['dependencies'],
					$asset['version'],
					$asset['meta']
				);
			}
		}
	}

	/**
	 * Retrieve the capability that is required for using the admin page.
	 *
	 * @since 2.1.2
	 *
	 * @return string Capability that must be met to use the Admin page.
	 */
	public function get_permission() {
		/**
		 * Allows the override of the default permission for per site domain management.
		 *
		 * @since 2.1.2
		 *
		 * @param string $capability Capability required to manage domains (upgrade_network / Super Admin).
		 * @param string $context The context the permission is checked.
		 */
		return apply_filters( 'dark_matter_domain_permission', 'upgrade_network', 'admin' );
	}

	/**
	 * Very basic HTML output for the
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function page() {
		if ( ! current_user_can( $this->get_permission() ) ) {
			wp_die( esc_html__( 'You do not have permission to manage domains.', 'dark-matter' ) );
		}
		?>
		<div id="root" data-admin-domain="<?php echo esc_url( get_home_url( null, '/', 'unmapped' ) ); ?>"></div>
		<?php
	}
}

new DM_UI();

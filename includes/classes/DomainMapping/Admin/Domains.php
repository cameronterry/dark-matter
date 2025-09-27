<?php
/**
 * Admin page for managing domains.
 *
 * @package DarkMatter
 *
 * @since 2.0.0
 */

namespace DarkMatterPlugin\DomainMapping\Admin;

use DarkMatterPlugin\Registerable;

/**
 * Class Domains
 *
 * @since 2.0.0
 */
class Domains implements Registerable {

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
	 * Register the Domains UI class.
	 *
	 * @return bool
	 */
	public function can_register() {
		return
			/**
			 * Dark Matter Plugin currently does not support domain mapping on the root site.
			 */
			! is_main_site()
			/**
			 * Maintain support for hiding the UI, used for sites where domain mapping is managed by either server
			 * admins using the CLI. Or installations using a custom integration through the REST API.
			 */
			&& ( ! defined( 'DARKMATTER_HIDE_UI' ) || ! DARKMATTER_HIDE_UI );
	}

	/**
	 * Enqueue assets for the Admin Page.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function enqueue() {
		$script_data = include DM_PATH . 'dist/app-script.asset.php';

		wp_enqueue_script(
			'darkmatterplugin-admin-script',
			DM_PLUGIN_URL . 'dist/app-script.js',
			$script_data['dependencies'],
			$script_data['version'],
			[
				'in_footer' => true,
			]
		);
		wp_enqueue_style(
			'darkmatterplugin-admin-style',
			DM_PLUGIN_URL . 'dist/app-style.css',
			[],
			$script_data['version'],
		);
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

	/**
	 * Handle actions and filters for the Domains UI.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}
}

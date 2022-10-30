<?php
/**
 * Settings page for controlling domains.
 *
 * @package DarkMatter\DomainMapping
 * @since 2.0.0
 */

namespace DarkMatter\DomainMapping\Admin;

use DarkMatter\UI\AbstractAdminPage;

/**
 * Class DomainSettings
 *
 * Previously called `DM_UI`.
 *
 * @since 2.0.0
 */
class DomainSettings extends AbstractAdminPage {
	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->slug       = 'domains';
		$this->menu_title = __( 'Domains', 'dark-matter' );
		$this->page_title = __( 'Domain Mappings', 'dark-matter' );

		/**
		 * Allows the override of the default permission for per site domain management.
		 *
		 * @since 2.1.2
		 *
		 * @param string $capability Capability required to manage domains (upgrade_network / Super Admin).
		 * @param string $context The context the permission is checked.
		 */
		$this->permission = apply_filters( 'dark_matter_domain_permission', 'upgrade_network', 'admin' );
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
	 * Very basic HTML output for the
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function render() {
		?>
		<div id="root"></div>
		<?php
	}
}

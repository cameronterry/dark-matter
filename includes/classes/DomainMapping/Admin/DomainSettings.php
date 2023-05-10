<?php
/**
 * Settings page for controlling domains.
 *
 * @since 3.0.0
 *
 * @package DarkMatterPlugin\DomainMapping
 */

namespace DarkMatter\DomainMapping\Admin;

use DarkMatter\UI\AbstractAdminPage;

/**
 * Class DomainSettings
 *
 * Based on the old class called `DM_UI`.
 *
 * @since 3.0.0
 */
class DomainSettings extends AbstractAdminPage {
	/**
	 * Constructor
	 *
	 * @since 3.0.0
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
		 * @param string $context    The context the permission is checked.
		 */
		$this->permission = apply_filters( 'dark_matter_domain_permission', 'upgrade_network', 'admin' );
	}

	/**
	 * Enqueue assets for the Admin Page.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function enqueue() {
		$asset = require DM_PATH . 'dist/domain-mapping.asset.php';

		wp_register_script(
			'dark-matter-domains',
			DM_PLUGIN_URL . 'dist/domain-mapping.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		$inline_data = [
			'endpoints' => [
				'domains' => 'dm/v1/domains',
			],
		];
		wp_add_inline_script(
			'dark-matter-domains',
			sprintf(
				'var dmp = %s;',
				wp_json_encode( $inline_data )
			)
		);

		wp_enqueue_script( 'dark-matter-domains' );

		wp_enqueue_style(
			'dark-matter-domains',
			DM_PLUGIN_URL . 'dist/domain-mapping.css',
			[],
			DM_VERSION
		);
	}

	/**
	 * Output the HTML container for the React app.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function render() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Domains', 'darkmatterplugin' ); ?></h1>
			<div id="root"></div>
		</div>
		<?php
	}
}

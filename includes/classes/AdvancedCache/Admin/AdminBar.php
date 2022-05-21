<?php
/**
 * Provides some functionality on the admin bar.
 *
 * @package DarkMatter\AdvancedCache
 */
namespace DarkMatter\AdvancedCache\Admin;

use DarkMatter\AdvancedCache\Data\Request;
use DarkMatter\Interfaces\Registerable;

/**
 * Class AdminBar
 */
class AdminBar implements Registerable {
	/**
	 * Retrieves the URL for the current request.
	 *
	 * @return string URL, minus the protocol.
	 */
	private function get_url() {
		/**
		 * Retrieve the current request.
		 */
		global $wp;
		$url = get_home_url( null, $wp->request );

		/**
		 * Remote the protocol.
		 */
		$protocol = wp_parse_url( $url, PHP_URL_SCHEME );
		return trailingslashit( str_replace( "{$protocol}://", '', $url ) );
	}

	/**
	 * Add Advanced Cache information and controls to the Admin Bar.
	 *
	 * @param \WP_Admin_Bar $admin_bar Admin Bar.
	 * @return void
	 */
	public function menu( $admin_bar = null ) {
		$request = new Request( $this->get_url() );

		/**
		 * Add the parent node.
		 */
		$admin_bar->add_menu(
			[
				'id'    => 'dark-matter-advancedcache',
				'title' => wp_kses(
					__( '<span class="ab-icon dashicons dashicons-info"></span> Advanced Cache', 'dark-matter' ),
					[
						'span' => [
							'class' => true,
						],
					]
				),
			]
		);

		$this->menu_variants( $admin_bar, $request );
	}

	/**
	 * Handle the admin bar options for Variants.
	 *
	 * @param \WP_Admin_Bar $admin_bar Admin Bar.
	 * @param Request       $request   Request.
	 * @return void
	 */
	private function menu_variants( $admin_bar, $request ) {
		$variants_count = count( $request->variants );

		$admin_bar->add_node(
			[
				'id'     => 'dark-matter-advancedcache-variants',
				'parent' => 'dark-matter-advancedcache',
				'title'  => sprintf(
					/* translators: %s: count of the number request variants. */
					_n( '%s variant stored', '%s variants stored', $variants_count, 'dark-matter' ),
					number_format_i18n( $variants_count )
				),
			]
		);
	}

	/**
	 * Handle hooks for the Admin Bar additions.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_bar_menu', [ $this, 'menu' ], 100 );
	}
}

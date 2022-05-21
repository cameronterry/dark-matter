<?php
/**
 * Provides some functionality on the admin bar.
 *
 * @package DarkMatter\AdvancedCache
 */
namespace DarkMatter\AdvancedCache\Admin;

use DarkMatter\AdvancedCache\Data\Request;
use DarkMatter\AdvancedCache\Data\ResponseEntry;
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
		$url = $this->get_url();

		$request = new Request( $url );
		$variants_count = count( $request->variants );

		/**
		 * Add the parent node.
		 */
		$admin_bar->add_menu(
			[
				'id'    => 'dark-matter-advancedcache',
				'title' => wp_kses(
					sprintf(
						/* translators: %s: count of the number request variants. */
						_n( 'Advanced Cache (%s variant)', 'Advanced Cache (%s variants)', $variants_count, 'dark-matter' ),
						number_format_i18n( $variants_count )
					),
					[
						'span' => [
							'class' => true,
						],
					]
				),
			]
		);

		$this->menu_variants( $admin_bar, $request );

		$admin_bar->add_node(
			[
				'id'     => 'dark-matter-advancedcache-clear',
				'parent' => 'dark-matter-advancedcache',
				'title'  => __( 'Clear Cache', 'dark-matter' ),
			]
		);
	}

	/**
	 * Add to the menu the individual options for the variants.
	 *
	 * @param \WP_Admin_Bar $admin_bar Admin Bar.
	 * @param Request       $request   Request.
	 * @return void
	 */
	private function menu_variant( $admin_bar, $request, $variant = '' ) {
		$response_entry = $request->get_variant( $variant );
		if ( empty( $response_entry ) ) {
			return;
		}

		$id = 'dark-matter-ac-variant-default';
		if ( empty( $variant ) ) {
			$id = 'dark-matter-ac-variant-' . esc_attr( $variant );
		}

		/**
		 * Add the Variant parent node.
		 */
		$admin_bar->add_node(
			[
				'id'     => $id,
				'parent' => 'dark-matter-advancedcache',
				'title'  => __( 'Variant: Default', 'dark-matter' ),
			]
		);

		/**
		 * Add the Expiry node.
		 */
		$expiry = __( 'None (Perpetual)', 'dark-matter' );
		if ( time() > $response_entry->expiry ) {
			$expiry = __( 'Expired', 'dark-matter' );
		} elseif ( 0 < $response_entry->expiry ) {
			$expiry = human_time_diff( $response_entry->expiry, wp_date( 'u' ) );
		}

		$admin_bar->add_node(
			[
				'id'     => $id . '-expiry',
				'parent' => $id,
				'title'  => sprintf(
					/* translators: %s: expiry time, in human readable (i.e. 5 minutes) */
					__( 'Expiry: %s', 'dark-matter' ),
					$expiry
				),
			]
		);

		$admin_bar->add_node(
			[
				'id'     => $id . '-clear',
				'parent' => $id,
				'title'  => __( 'Clear Variant', 'dark-matter' ),
			]
		);
	}

	/**
	 * Handle the admin bar options for Variants.
	 *
	 * @param \WP_Admin_Bar $admin_bar Admin Bar.
	 * @param Request       $request   Request.
	 * @return void
	 */
	private function menu_variants( $admin_bar, $request ) {
		$this->menu_variant( $admin_bar, $request );

		foreach ( $request->variants as $variant => $value ) {
			/**
			 * Get the response entry and ensure it hasn't been removed.
			 */
			$response_entry = $request->get_variant( $variant );
			if ( empty( $response_entry ) ) {
				continue;
			}

			$id = 'dark-matter-advancedcache-' . esc_attr( $variant );

			$admin_bar->add_node(
				[
					'id'     => $id,
					'parent' => 'dark-matter-advancedcache',
					'title'  => sprintf(
						/* translators: %s: variant name. */
						__( 'Variant: %s', 'dark-matter' ),
						$variant
					),
				]
			);

			$expiry = __( 'None (Perpetual)', 'dark-matter' );
			if ( 0 < $response_entry->expiry ) {
				$expiry = human_time_diff( $response_entry->expiry, wp_date( 'u' ) );
			}

			$admin_bar->add_node(
				[
					'id'     => $id . '-expiry',
					'parent' => $id,
					'title'  => sprintf(
						/* translators: %s: expiry time, in human readable (i.e. 5 minutes) */
						__( 'Expiry: %s', 'dark-matter' ),
						$expiry
					),
				]
			);
		}
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

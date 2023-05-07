<?php
/**
 * Provides single sign-on action links with the admin UI for admins / editors.
 *
 * @package DarkMatterPlugin\SSO
 */

namespace DarkMatter\SSO;

use DarkMatter\Interfaces\Registerable;

/**
 * Class Admin
 */
class Admin implements Registerable {
	/**
	 * Add an option to log in to the site on the admin bar.
	 *
	 * @param \WP_Admin_Bar $admin_bar Admin bar object.
	 * @return \WP_Admin_Bar
	 */
	public function admin_bar( $admin_bar ) {
		if ( ! $admin_bar instanceof \WP_Admin_Bar ) {
			return $admin_bar;
		}

		$check_url = Authorise::get_check_url();
		if ( empty( $check_url ) ) {
			return $admin_bar;
		}

		$admin_bar->add_node(
			[
				'id'     => 'dark-matter-plugin-signin',
				'href'   => $check_url,
				'parent' => 'site-name',
				'title'  => __( 'Log into Site', 'dark-matter-plugin' ),
			]
		);

		return $admin_bar;
	}

	/**
	 * Handle actions and filters for the SSO admin.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_bar_menu', [ $this, 'admin_bar' ], 40 );
	}
}

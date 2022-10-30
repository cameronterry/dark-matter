<?php
/**
 * Helper class for adding admin pages.
 *
 * @since 3.0.0
 * @package DarkMatter
 */

namespace DarkMatter\UI;

use DarkMatter\Interfaces\Registerable;

/**
 * Class AbstractAdminPage
 *
 * @since 3.0.0
 */
abstract class AbstractAdminPage implements Registerable {
	/**
	 * Menu Title
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected $menu_title = '';

	/**
	 * Page Title
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected $page_title = '';

	/**
	 * Parent Slug (defaults to "Settings" / `options-general.php`)
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected $parent_slug = 'options-general.php';

	/**
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected $permission = '';

	/**
	 * Slug
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected $slug = '';

	/**
	 * Add the menu item to the page.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function admin_menu() {
		$hook_suffix = add_submenu_page(
			$this->parent_slug,
			$this->page_title,
			$this->menu_title,
			$this->permission,
			$this->slug,
			[ $this, 'page' ]
		);

		add_action( 'load-' . $hook_suffix, [ $this, 'enqueue' ] );
	}

	/**
	 * Used for enqueuing custom scripts and styles.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	abstract public function enqueue();

	/**
	 * Handle the rendering of the page.
	 *
	 * @return void
	 */
	public function page() {
		if ( ! current_user_can( $this->permission ) ) {
			wp_die( esc_html__( 'You do not have permission to manage domains.', 'dark-matter' ) );
		}

		$this->render();
	}

	/**
	 * Produce the HTML output of the admin page.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	abstract public function render();

	/**
	 * Register hooks for this class.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}
}

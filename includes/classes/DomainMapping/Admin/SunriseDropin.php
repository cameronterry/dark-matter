<?php
/**
 * Checks and provides an actionable notice for administrators relating to updates for the `sunrise.php` dropin plugin.
 *
 * @package DarkMatterPlugin
 */

namespace DarkMatterPlugin\DomainMapping\Admin;

use DarkMatterPlugin\Registerable;

/**
 * Class SunriseDropin
 */
class SunriseDropin implements Registerable {

	/**
	 * Minimum permission capability to perform updates/handling for Sunrise dropin plugin.
	 *
	 * @var string
	 */
	private $permission_cap = 'manage_options';

	/**
	 * Register this class.
	 *
	 * @return bool
	 */
	public function can_register() {
		return current_user_can( $this->permission_cap );
	}

	/**
	 * Determines which version of the `sunrise.php` dropin is used.
	 *
	 * @return string
	 */
	public function get_dropin_version() {
		$installed = WP_CONTENT_DIR . '/sunrise.php';
		if ( ! file_exists( $installed ) ) {
			return 'notfound';
		}

		$current = DM_PATH . 'includes/dropins/sunrise.php';
		$legacy  = DM_PATH . 'inc/legacy-sunrise-dropin.php';

		$installed_size = filesize( $installed );
		$installed_md5  = md5_file( $installed );

		if ( $installed_size === filesize( $current ) && $installed_md5 === md5_file( $current ) ) {
			return 'latest';
		} elseif ( $installed_size === filesize( $legacy ) && $installed_md5 === md5_file( $legacy ) ) {
			return 'legacy';
		}

		return 'unknown';
	}

	/**
	 * Show a notice to applicable users who can perform an update for the Sunrise plugin.
	 *
	 * @return void
	 */
	public function maybe_show_notice() {
		/**
		 * Used by third party developer's to disable the check for the Sunrise dropin plugin. This can be because the
		 * file is included in another manner or has been customised to include additional logic beyond Dark Matter
		 * Plugin.
		 *
		 * @param bool $disable True to disable sunrise check. False otherwise.
		 * @return bool
		 */
		if ( apply_filters( 'darkmatterplugin_sunrise_disable_check', false ) ) {
			return;
		}

		if ( ! current_user_can( $this->permission_cap ) ) {
			return;
		}

		$message = '';

		/**
		 * Adjust the message depending on the situation.
		 */
		$version = $this->get_dropin_version();
		if ( 'latest' === $version ) {
			return;
		} elseif ( 'notfound' === $version ) {
			$message = __( 'Dark Matter Plugin: Sunrise dropin plugin could not be found.', 'darkmatterplugin' );
		} elseif( 'legacy' === $version ) {
			$message = __( 'Dark Matter Plugin: An update is available for the Sunrise dropin plugin.', 'darkmatterplugin' );
		}

		if ( empty( $message ) ) {
			return;
		}

		/**
		 * If possible, provide a way for the user to update sunrise dropin.
		 */
		if ( is_writable( WP_CONTENT_DIR . '/sunrise.php' ) ) {
			$action = sprintf(
				/* translators: %s: URL used to perform the action. */
				__( 'You can update the Sunrise dropin plugin by <a href="%s">clicking here</a>.', 'darkmatterplugin' ),
				wp_nonce_url(
					admin_url( '/admin-post.php?action=darkmatterplugin_update_dropin' ),
					'darkmatterplugin_update_dropin'
				)
			);
		} else {
			$action = __( 'It is not possible to update Sunrise dropin. Please notify your system administrator or developer.', 'darkmatterplugin' );
		}

		?>
		<div class="notice notice-error">
			<?php
			echo wp_kses(
				sprintf(
					'<p>%s</p><p>%s</p>',
					$message,
					$action
				),
				[
					'a' => [
						'href'  => [],
						'title' => [],
					],
					'p' => [],
				]
			);
			?>
		</div>
		<?php
	}

	/**
	 * Handle the sunrise update actions from the notification.
	 *
	 * @return void
	 */
	public function handle_update() {
		/**
		 * Restrict this action to administrator's only.
		 */
		if ( ! current_user_can( $this->permission_cap ) ) {
			wp_die(
				__( 'Sorry, you are not allowed to update Sunrise by Dark Matter Plugin.', 'darkmatterplugin' ),
			);
		}

		$nonce = wp_unslash( sanitize_text_field( $_GET['_wpnonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'darkmatterplugin_update_dropin' ) ) {
			wp_die(
				__( 'Sorry, we are unable to handle this request.', 'darkmatterplugin' ),
			);
		}

		/**
		 * If possible, return the administrator to the page they were on previously. If this cannot be determined, for
		 * whatever reason, then we opt to return to the admin dashboard.
		 */
		$referer = wp_get_referer();
		if ( empty( $referer ) ) {
			$referer = admin_url();
		}

		wp_safe_redirect(
			add_query_arg(
				[
					'darkmatterplugin_sunrise' => 'updated',
				],
				$referer
			)
		);
		die;
	}

	/**
	 * Handle actions and filters for the Sunrise Dropin checks and handling.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_notices', [ $this, 'maybe_show_notice' ] );
		add_action( 'admin_post_darkmatterplugin_update_dropin', [ $this, 'handle_update' ] );
	}
}

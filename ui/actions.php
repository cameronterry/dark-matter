<?php

function dark_matter_actions_add_domain() {
	/** Validate the nonce before proceeding. */
	if ( array_key_exists( 'dm_new_nonce', $_POST ) && false === wp_verify_nonce( $_POST['dm_new_nonce'], 'darkmatter-add-domain' ) ) {
		wp_die( 'Unable to add Domain Mapping to this blog due to an unknown error.' );
	}

	/** Make sure at the very least, a domain was supplied (won't get far otherwise!). */
	if ( array_key_exists( 'dm_new_domain', $_POST ) && false === empty( $_POST['dm_new_domain'] ) ) {
		$redirect_url = admin_url( 'options-general.php' );
		$redirect_url = add_query_arg( 'page', 'dark_matter_blog_settings', $redirect_url );

		/** Get the domain flags. */
		$is_primary = ( array_key_exists( 'dm_new_is_primary', $_POST ) && 'yes' === $_POST['dm_new_is_primary'] );
	    $is_https = ( array_key_exists( 'dm_new_is_https', $_POST ) && 'yes' === $_POST['dm_new_is_https'] );

		/** Get the domain and sanitise it. */
		$domain = strip_tags( $_POST['dm_new_domain'] );

		/**
		 * Or should it sanitise using the following; Currently commented out as I am unsure
		 * if this would work in relation to URLs which contain Arabic or Chinese for example.
		 * To be honest, not entirely sure how WordPress will function, never mind this measly
		 * plugin! Actually, does PHP cope with it given the issues with Unicode ...
		 */
		//$domain = strip_tags( sanitize_title( $_POST['dm_new_domain'], '', 'save' ) );

		/**
		 * Check to make sure that a) the new domain isn't already in use and b) that it is
		 * not mapped to another website within this WordPress Network.
		 */
		if ( 0 < dark_matter_api_domain_exists( $domain ) ) {
			$redirect_url = add_query_arg( array(
				'domain' => esc_url( $domain ),
				'message' => 'in_use'
			), $redirect_url );

			wp_safe_redirect( $redirect_url );
			die();
		}

		/**
		 * Add the domain to the database and then handle a redirect depending on whether
		 * the database insert was successful or unsuccessful.
		 */
		$new_domain = dark_matter_api_add_domain( $domain, $is_primary, $is_https );

		if ( false === $new_domain ) {
			$redirect_url = add_query_arg( array(
				'domain' => esc_url( $domain ),
				'message' => 'failed'
			), $redirect_url );

			wp_safe_redirect( $redirect_url );
			die();
		}
		else {
			$redirect_url = add_query_arg( array(
				'domain' => esc_url( $domain ),
				'message' => 'success_added'
			), $redirect_url );

			wp_safe_redirect( $redirect_url );
			die();
		}
	}

	wp_die( 'An unexpected error with Domain Mapping has occurred.' );
}
add_action( 'admin_action_dm_add_domain', 'dark_matter_actions_add_domain' );

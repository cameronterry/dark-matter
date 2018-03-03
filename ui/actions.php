<?php

function dark_matter_actions_add_domain() {
    if ( false === current_user_can( 'activate_plugins' ) ) {
        wp_die( __( 'Insufficient permissions.', 'dark-matter' ) );
    }

    /** Validate the nonce before proceeding. */
    if ( array_key_exists( 'dm_new_nonce', $_POST ) && false === wp_verify_nonce( $_POST['dm_new_nonce'], 'darkmatter-add-domain' ) ) {
        wp_die( __( 'Unable to add Domain Mapping to this blog due to an unknown error.', 'dark-matter' ) );
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
            $message = 'in_use';
        }
        else {
            /**
             * Add the domain to the database and then handle a redirect depending on whether
             * the database insert was successful or unsuccessful.
             */
            $new_domain = dark_matter_api_add_domain( $domain, $is_primary, $is_https );

            if ( false === $new_domain ) {
                $message = 'failed';
            }
            else {
                $message = 'success_added';
            }
        }

        $redirect_url = add_query_arg( array(
            'domain' => esc_url( $domain ),
            'message' => $message
        ), $redirect_url );

        wp_safe_redirect( $redirect_url );
        die();
    }

    wp_die( __( 'An unexpected error with Domain Mapping has occurred.', 'dark-matter' ) );
}
add_action( 'admin_action_dm_add_domain', 'dark_matter_actions_add_domain' );

function dark_matter_actions_delete_domain() {
    if ( false === current_user_can( 'activate_plugins' ) ) {
        wp_die( __( 'Insufficient permissions.', 'dark-matter' ) );
    }

    /** Validate the nonce before proceeding. */
    if ( array_key_exists( 'dm_del_nonce', $_GET ) && false === wp_verify_nonce( $_GET['dm_del_nonce'], 'darkmatter-delete-domain' ) ) {
        wp_die( __( 'Unable to delete domain for this blog due to an unknown error.', 'dark-matter' ) );
    }

    $redirect_url = admin_url( 'options-general.php' );
    $redirect_url = add_query_arg( 'page', 'dark_matter_blog_settings', $redirect_url );

    $message = 'success_deleted';

    if ( false === dark_matter_api_del_domain( $_GET['id'] ) ) {
        $message = 'fail_deleted';
    }

    $redirect_url = add_query_arg( array(
        'message' => $message
    ), $redirect_url );

    wp_safe_redirect( $redirect_url );

    die();
}
add_action( 'admin_action_dm_del_domain', 'dark_matter_actions_delete_domain' );

function dark_matter_actions_new_primary_domain() {
    if ( false === current_user_can( 'activate_plugins' ) ) {
        wp_die( __( 'Insufficient permissions.', 'dark-matter' ) );
    }

    /** Validate the nonce before proceeding. */
    if ( array_key_exists( 'dm_new_primary_nonce', $_GET ) && false === wp_verify_nonce( $_GET['dm_new_primary_nonce'], 'darkmatter-new-primary-domain' ) ) {
        wp_die( __( 'Unable to delete domain for this blog due to an unknown error.', 'dark-matter' ) );
    }

    $redirect_url = admin_url( 'options-general.php' );
    $redirect_url = add_query_arg( 'page', 'dark_matter_blog_settings', $redirect_url );

    $message = 'success_new_primary';

    if ( false === dark_matter_api_set_domain_primary( intval( $_GET['id'] ) ) ) {
        $message = 'failed_new_primary';
    }

    wp_safe_redirect( add_query_arg( array(
        'message' => $message
    ), $redirect_url ) );

    die();
}
add_action( 'admin_action_dm_new_primary_domain', 'dark_matter_actions_new_primary_domain' );

function dark_matter_actions_set_domain_https() {
    if ( false === current_user_can( 'activate_plugins' ) ) {
        wp_die( __( 'Insufficient permissions.', 'dark-matter' ) );
    }

    /** Validate the nonce before proceeding. */
    if ( array_key_exists( 'dm_set_https_nonce', $_GET ) && false === wp_verify_nonce( $_GET['dm_set_https_nonce'], 'darkmatter-set-https-domain' ) ) {
        wp_die( __( 'Unable to delete domain for this blog due to an unknown error.', 'dark-matter' ) );
    }

    $redirect_url = admin_url( 'options-general.php' );
    $redirect_url = add_query_arg( 'page', 'dark_matter_blog_settings', $redirect_url );

    $message = 'success_set_https';

    if ( false === dark_matter_api_set_domain_https( intval( $_GET['id'] ) ) ) {
        $message = 'failed_set_https';
    }

    wp_safe_redirect( add_query_arg( array(
        'message' => $message
    ), $redirect_url ) );

    die();
}
add_action( 'admin_action_dm_set_domain_https', 'dark_matter_actions_set_domain_https' );

function dark_matter_actions_unset_domain_https() {
    if ( false === current_user_can( 'activate_plugins' ) ) {
        wp_die( __( 'Insufficient permissions.', 'dark-matter' ) );
    }

    /** Validate the nonce before proceeding. */
    if ( array_key_exists( 'dm_set_https_nonce', $_GET ) && false === wp_verify_nonce( $_GET['dm_set_https_nonce'], 'darkmatter-set-https-domain' ) ) {
        wp_die( __( 'Unable to delete domain for this blog due to an unknown error.', 'dark-matter' ) );
    }

    $redirect_url = admin_url( 'options-general.php' );
    $redirect_url = add_query_arg( 'page', 'dark_matter_blog_settings', $redirect_url );

    $message = 'success_unset_https';

    if ( false === dark_matter_api_unset_domain_https( intval( $_GET['id'] ) ) ) {
        $message = 'failed_unset_https';
    }

    wp_safe_redirect( add_query_arg( array(
        'message' => $message
    ), $redirect_url ) );

    die();
}
add_action( 'admin_action_dm_unset_domain_https', 'dark_matter_actions_unset_domain_https' );

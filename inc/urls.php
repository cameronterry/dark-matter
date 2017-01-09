<?php

defined( 'ABSPATH' ) or die();

/**
 * If the Network Admin, then we just get out as the domain mapping is causing
 * a variety of errors which are related to the fact the Network admin is all
 * sites and not a specific website.
 */
if ( is_network_admin() ) {
	return;
}

function dark_matter_map_url( $setting ) {
	global $current_blog;

	$protocol = ( $current_blog->https ? 'https://' : 'http://' );

	return sprintf( '%1$s%2$s', $protocol, $current_blog->domain );
}

function dark_matter_map_content( $content ) {
	global $current_blog;

	$protocol = ( $current_blog->https ? 'https://' : 'http://' );
	$domain = sprintf( '%1$s%2$s/', $protocol, $current_blog->domain );

	if ( is_string( $content ) ) {
		$content = preg_replace( "#https?://{$current_blog->original_domain}#", $domain, $content );
	}
	elseif ( is_array( $content ) ) {
		foreach ( $content as $key => $value ) {
			if ( is_string( $value ) ) {
				$content[$key] = preg_replace( "#https?://{$current_blog->original_domain}#", $domain, $value );
			}
		}
	}

	return $content;
}

if ( defined( 'DOMAIN_MAPPING' ) && DOMAIN_MAPPING ) {
	/**
	 * This is to make sure that links and requests using admin_url() template
	 * tag get the actual Admin URL. This is also solves a problem when the admin
	 * is HTTPS but the mapped domain is HTTP.
	 */
	add_filter( 'admin_url', 'dark_matter_api_unmap_permalink' );

	add_filter( 'pre_option_siteurl', 'dark_matter_map_url' );
	add_filter( 'pre_option_home', 'dark_matter_map_url' );

	add_filter( 'the_content', 'dark_matter_map_content' );
	add_filter( 'stylesheet_uri', 'dark_matter_map_content' );
	add_filter( 'stylesheet_directory_uri', 'dark_matter_map_content' );
	add_filter( 'template_directory_uri', 'dark_matter_map_content' );
	add_filter( 'plugins_url', 'dark_matter_map_content' );
	add_filter( 'upload_dir', 'dark_matter_map_content' );
	add_filter( 'wp_get_attachment_url', 'dark_matter_map_content' );
}

/**
 * WP-ADMIN adjustments. This is to ensure that the various elements which should
 * be displaying the mapped domain ... do!
 */
function dark_matter_map_admin_permalink() {
	add_filter( 'page_link', 'dark_matter_api_map_permalink' );
	add_filter( 'post_link', 'dark_matter_api_map_permalink' );
}
add_action( 'edit_form_before_permalink', 'dark_matter_map_admin_permalink' );

function dark_matter_unmap_admin_permalink() {
	remove_filter( 'page_link', 'dark_matter_api_map_permalink' );
	remove_filter( 'post_link', 'dark_matter_api_map_permalink' );
}
add_action( 'edit_form_after_title', 'dark_matter_unmap_admin_permalink' );

function dark_matter_map_admin_comments_permalink() {
	add_filter( 'page_link', 'dark_matter_api_map_permalink' );
	add_filter( 'post_link', 'dark_matter_api_map_permalink' );
}
add_action( 'manage_comments_nav', 'dark_matter_map_admin_comments_permalink' );

function dark_matter_map_admin_ajax_sample_permalink() {
	add_filter( 'get_sample_permalink', 'dark_matter_api_map_permalink' );
}
add_action( 'wp_ajax_sample-permalink', 'dark_matter_map_admin_ajax_sample_permalink', 0 );

function dark_matter_map_admin_ajax_query_attachments() {
	add_filter( 'attachment_link', 'dark_matter_api_map_permalink' );
}
add_action( 'wp_ajax_query-attachments', 'dark_matter_map_admin_ajax_query_attachments', 0 );

function dark_matter_wp_prepare_attachment_for_js( $response ) {
	if ( array_key_exists( 'url', $response ) ) {
		$response['url'] = dark_matter_api_map_permalink( $response['url'] );
	}

	if ( array_key_exists( 'sizes', $response ) ) {
		$sizes = array_keys( $response['sizes'] );

		foreach ( $sizes as $size ) {
			if ( array_key_exists( 'url', $response['sizes'][$size] ) ) {
				$response['sizes'][$size]['url'] = dark_matter_api_map_permalink( $response['sizes'][$size]['url'] );
			}
		}
	}

	return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'dark_matter_wp_prepare_attachment_for_js' );

function dark_matter_post_row_actions( $actions ) {
	if ( array_key_exists( 'view', $actions ) && false === strpos( $actions['view'], 'preview=true' ) ) {
		$actions['view'] = dark_matter_api_map_permalink( $actions['view'] );
	}

	return $actions;
}

function dark_matter_admin_pre_option_home( $value ) {
	global $current_blog;

	$original_domain = dark_matter_api_get_domain_original();

	$primary_domain = dark_matter_api_get_domain_primary();

	if ( empty( $primary_domain ) ) {
		return false;
	}

	$protocol = ( $current_blog->https ? 'https://' : 'http://' );
	$domain = sprintf( '%1$s%2$s', $protocol, $primary_domain );

	return $domain;
}

/**
 * For the admin side filters, we attached when both WordPress being loaded into
 * the admin area and for wp-cron as well. This ensures that functionality which
 * depends on getting the mapped domain gets it correctly.
 */
if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) && property_exists( $current_blog, 'primary_domain' ) ) {
	add_filter( 'pre_option_home', 'dark_matter_admin_pre_option_home' );

	add_filter( 'comment_row_actions', 'dark_matter_post_row_actions' );
	add_filter( 'post_row_actions', 'dark_matter_post_row_actions' );
	add_filter( 'media_row_actions', 'dark_matter_post_row_actions' );
	add_filter( 'page_row_actions', 'dark_matter_post_row_actions' );
	add_filter( 'tag_row_actions', 'dark_matter_post_row_actions' );

	add_filter( 'get_comment_author_url', 'dark_matter_api_map_permalink' );

	add_filter( 'preview_post_link', 'dark_matter_api_unmap_permalink' );
}

function dark_matter_allowed_redirect_hosts( $allowed_hosts ) {
	global $current_blog;

	/**
	 * Only include this if its a sub-domain setup. It appears WordPress is only
	 * adding the root domain and not adding the additional sub-domains. Not
	 * sure right now if that is unique to the development setup for Dark Matter
	 * or WordPress in general.
	 */
	if ( property_exists( $current_blog, 'original_domain' ) && defined( 'SUBDOMAIN_INSTALL' ) && SUBDOMAIN_INSTALL ) {
		$allowed_hosts[] = untrailingslashit( $current_blog->original_domain );
	}

	/**
	 * Add the primary domain to the allow list. As it is part of the website
	 * then it makes sense. For now, the other mapped domains are excluded as
	 * they should be redirecting through sunrise.php to the primary domain.
	 */
	if ( property_exists( $current_blog, 'primary_domain' ) ) {
		$allowed_hosts[] = $current_blog->primary_domain;
	}

	return $allowed_hosts;
}
add_filter( 'allowed_redirect_hosts', 'dark_matter_allowed_redirect_hosts' );

<?php

function dark_matter_map_url( $setting ) {
  global $current_blog;

  $protocol = ( is_ssl() ? 'https://' : 'http://' );

  return sprintf( '%1$s%2$s', $protocol, $current_blog->domain );
}

function dark_matter_map_content( $content ) {
  global $current_blog;

  $protocol = ( is_ssl() ? 'https://' : 'http://' );
  $domain = sprintf( '%1$s%2$s/', $protocol, $current_blog->domain );

  if ( is_string( $domain ) ) {
    $content = preg_replace( "#http?://{$current_blog->original_domain}#", $domain, $content );
  }
  elseif ( is_array( $content ) ) {
    foreach ( $content as $key => $value ) {
      $content[$key] = preg_replace( "#http?://{$current_blog->original_domain}#", $domain, $value );
    }
  }

  return $content;
}

if ( defined( 'DOMAIN_MAPPING' ) && DOMAIN_MAPPING ) {
  add_filter( 'pre_option_siteurl', 'dark_matter_map_url' );
  add_filter( 'pre_option_home', 'dark_matter_map_url' );

  add_filter( 'the_content', 'dark_matter_map_content' );
  add_filter( 'stylesheet_uri', 'dark_matter_map_content' );
  add_filter( 'stylesheet_directory', 'dark_matter_map_content' );
  add_filter( 'stylesheet_directory_uri', 'dark_matter_map_content' );
  add_filter( 'template_directory', 'dark_matter_map_content' );
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
  return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'dark_matter_wp_prepare_attachment_for_js' );

function dark_matter_post_row_actions( $actions ) {
  if ( array_key_exists( 'view', $actions ) && false === strpos( $actions['view'], 'preview=true' ) ) {
    $actions['view'] = dark_matter_api_map_permalink( $actions['view'] );
  }

  return $actions;
}

function dark_matter_admin_pre_option_home() {
  $primary_domain = dark_matter_api_get_domain_primary();

  if ( empty( $primary_domain ) ) {
    return false;
  }

  $protocol = ( false ? 'https://' : 'http://' );
  $domain = sprintf( '%1$s%2$s', $protocol, $primary_domain );

  return $domain;
}

if ( is_admin() ) {
  add_filter( 'pre_option_home', 'dark_matter_admin_pre_option_home' );

  add_filter( 'comment_row_actions', 'dark_matter_post_row_actions' );
  add_filter( 'post_row_actions', 'dark_matter_post_row_actions' );
  add_filter( 'media_row_actions', 'dark_matter_post_row_actions' );
  add_filter( 'page_row_actions', 'dark_matter_post_row_actions' );
  add_filter( 'tag_row_actions', 'dark_matter_post_row_actions' );

  add_filter( 'get_comment_author_url', 'dark_matter_api_map_permalink' );

  add_filter( 'preview_post_link', 'dark_matter_api_unmap_permalink' );
}

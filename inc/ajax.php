<?php

function dark_matter_ajax_add_domain() {
  check_ajax_referer( 'nonce', 'dm_new_nonce' );
  $domain = $_POST['dm_new_domain'];
  $is_primary = ( array_key_exists( 'dm_new_is_primary', $_POST ) && 'yes' === $_POST['dm_new_is_primary'] );
  $is_https = ( array_key_exists( 'dm_new_is_https', $_POST ) && 'yes' === $_POST['dm_new_is_https'] );

  $new_domain = dark_matter_api_add_domain( $domain, $is_primary, $is_https );

  if ( false === $new_domain ) {
    echo( 'error' );
  }
  else {
    dark_matter_blog_domain_mapping_row( $new_domain );
  }
  
  die();
}
add_action( 'wp_ajax_dark_matter_add_domain', 'dark_matter_ajax_add_domain' );

function dark_matter_ajax_delete_domain() {
  check_ajax_referer( 'delete_nonce', 'dm_delete_nonce' );

  if ( false === dark_matter_api_del_domain( $_POST['id'] ) ) {
    wp_send_json_error();
  }
  else {
    wp_send_json_success();
  }
}
add_action( 'wp_ajax_dark_matter_del_domain', 'dark_matter_ajax_delete_domain' );

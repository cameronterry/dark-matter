<?php

function dark_matter_api_add_domain( $domain = '', $is_primary = false ) {
  global $wpdb;

  /** No domain provided, then we will crap out. */
  if ( empty( $domain ) ) {
    return false;
  }

  $insert = $wpdb->insert( $wpdb->dmtable, array(
    'blog_id' => get_current_blog_id(),
    //'primary' => $is_primary,
    'domain' => $domain,
    'active' => true
  ) );

  if ( false === $insert ) {
    return false;
  }
  else {
    return dark_matter_api_get_domain( $domain );
  }
}

function dark_matter_api_del_domain( $id ) {
  global $wpdb;

  return $wpdb->delete( $wpdb->dmtable, array(
    'id' => $id
  ) );
}

function dark_matter_api_get_domain( $identifier = null, $domain_only = false ) {
  global $wpdb;
  $sql = "SELECT * FROM {$wpdb->dmtable} WHERE ";

  if ( null === $identifier && empty( $identifier ) ) {
    return false;
  }
  else if ( is_numeric( $identifier ) ) {
    return $wpdb->get_row( $wpdb->prepare( $sql . 'id = %s', $identifier ) );
  }
  else {
    return $wpdb->get_row( $wpdb->prepare( $sql . 'domain = %s', $identifier ) );
  }
}

function dark_matter_api_get_domain_original() {
  global $current_blog;

  if ( property_exists( $current_blog, 'original_domain' ) ) {
    return $current_blog->original_domain;
  }

  return $current_blog->domain . $current_blog->path;
}

function dark_matter_api_get_domain_primary( $blog_id ) {
  $blog_id = ( null === $blog_id ? get_current_blog_id() : $blog_id );

  global $wpdb;
  return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->dmtable} WHERE blog_id = %s LIMIT 0, 1", $blog_id ) );
}

function dark_matter_api_get_domains( $blog_id = null ) {
  $blog_id = ( null === $blog_id ? get_current_blog_id() : $blog_id );

  global $wpdb;
  return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->dmtable} WHERE blog_id = %s", $blog_id ) );
}

<?php

defined( 'ABSPATH' ) or die();

function dark_matter_api_add_domain( $domain = '', $is_primary = false, $is_https = false ) {
  global $wpdb;

  /** No domain provided, then we will crap out. */
  if ( empty( $domain ) ) {
    return false;
  }

  dark_matter_api_unset_domain_primary();

  $insert = $wpdb->insert( $wpdb->dmtable, array(
    'blog_id' => get_current_blog_id(),
    'is_primary' => $is_primary,
    'is_https' => $is_https,
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

function dark_matter_api_domain_exists( $domain ) {
  global $wpdb;
  return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$wpdb->dmtable} WHERE domain = %s", $domain ) );
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

function dark_matter_api_get_domain_primary( $blog_id = null ) {
	global $current_blog;

    if ( property_exists( $current_blog, 'primary_domain' ) ) {
      return $current_blog->primary_domain;
    }

    return dark_matter_api_get_domain_original();
}

function dark_matter_api_get_domains( $blog_id = null ) {
  $blog_id = ( null === $blog_id ? get_current_blog_id() : $blog_id );

  global $wpdb;
  return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->dmtable} WHERE blog_id = %s", $blog_id ) );
}

function dark_matter_api_map_permalink( $permalink ) {
  global $current_blog;

  $original_domain = untrailingslashit( $current_blog->original_domain );
  $primary_domain = dark_matter_api_get_domain_primary();

  if ( empty( $primary_domain ) ) {
    return $permalink;
  }

  $protocol = ( $current_blog->https ? 'https://' : 'http://' );
  $domain = sprintf( '%1$s%2$s/', $protocol, $primary_domain );

  return preg_replace( "#https?://{$original_domain}#", $domain, $permalink );
}

function dark_matter_api_unmap_permalink( $permalink ) {
  global $current_blog;

  $original_domain = untrailingslashit( $current_blog->original_domain );
  $primary_domain = dark_matter_api_get_domain_primary();

  if ( empty( $primary_domain ) ) {
    return $permalink;
  }

  $protocol = ( $current_blog->https ? 'https://' : 'http://' );
  $domain = sprintf( '%1$s%2$s', $protocol, $original_domain );

  return preg_replace( "#https?://{$primary_domain}#", $domain, $permalink );
}

function dark_matter_api_set_domain_https( $domain_id = null ) {
	global $wpdb;

	return $wpdb->update( $wpdb->dmtable, array(
		'is_https' => 1
	), array(
		'id' => $domain_id
	) );
}

function dark_matter_api_unset_domain_https( $domain_id = null ) {
	global $wpdb;

	return $wpdb->update( $wpdb->dmtable, array(
		'is_https' => 0
	), array(
		'id' => $domain_id
	) );
}

function dark_matter_api_set_domain_primary( $domain_id = null ) {
	global $wpdb;

	dark_matter_api_unset_domain_primary();

	return $wpdb->update( $wpdb->dmtable, array(
		'is_primary' => 1
	), array(
		'id' => $domain_id
	) );
}

function dark_matter_api_unset_domain_primary( $domain = null ) {
	global $wpdb;

	/**
	 * Get the old domain and then unset the primary flag.
	 */
	$primary_domain = ( null === $domain ? dark_matter_api_get_domain_primary() : $domain );

	if ( false === empty( $primary_domain ) ) {
		return $wpdb->update( $wpdb->dmtable, array(
			'is_primary' => 0
		), array(
			'blog_id' => get_current_blog_id(),
			'domain' => $primary_domain
		) );
	}
}

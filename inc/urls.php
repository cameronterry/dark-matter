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
  add_filter( 'wp_get_attachment_url', 'dark_matter_map_content' );
}

<?php

function dark_matter_map_url( $setting ) {
  if ( defined( 'DOMAIN_MAPPING' ) && DOMAIN_MAPPING ) {
    global $current_blog;

    $protocol = ( is_ssl() ? 'https://' : 'http://' );

    return sprintf( '%1$s%2$s', $protocol, $current_blog->domain );
  }

  return $setting;
}
add_filter( 'pre_option_siteurl', 'dark_matter_map_url' );
add_filter( 'pre_option_home', 'dark_matter_map_url' );

function dark_matter_map_content( $content ) {
  if ( defined( 'DOMAIN_MAPPING' ) && DOMAIN_MAPPING ) {
    global $current_blog;

    $protocol = ( is_ssl() ? 'https://' : 'http://' );
    $domain = sprintf( '%1$s%2$s/', $protocol, $current_blog->domain );
    $content = preg_replace( "#http?://{$current_blog->original_domain}#", $domain, $content );

    return $content;
  }

  return $content;
}
add_filter( 'the_content', 'dark_matter_map_content' );

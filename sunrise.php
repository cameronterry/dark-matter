<?php

/** A bit of security for those who are too clever for their own good. */
defined( 'ABSPATH' ) or die();

if ( defined( 'COOKIE_DOMAIN' ) ) {
  wp_die( 'Multiple domain and sign-on is an interesting experience with a single ... defined domain ...' );
}

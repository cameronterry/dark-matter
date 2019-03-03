<?php

defined( 'ABSPATH' ) || die;

class DarkMatter_Dropin_CLI {
}
WP_CLI::add_command( 'darkmatter dropin', 'DarkMatter_Dropin_CLI' );
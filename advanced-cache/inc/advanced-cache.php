<?php
defined( 'ABSPATH' ) || die;

class DM_Advanced_Cache {
    public function __construct() {
        ob_start( array( $this, 'start' ) );
    }

    public function start( $output = '' ) {
        $position = stripos( $output, '<head>' );

        $debug = <<<HTML
<!--
________  ________  ________  ___  __            _____ ______   ________  _________  _________  _______   ________
|\   ___ \|\   __  \|\   __  \|\  \|\  \         |\   _ \  _   \|\   __  \|\___   ___\\___   ___\\  ___ \ |\   __  \
\ \  \_|\ \ \  \|\  \ \  \|\  \ \  \/  /|_       \ \  \\\__\ \  \ \  \|\  \|___ \  \_\|___ \  \_\ \   __/|\ \  \|\  \
 \ \  \ \\ \ \   __  \ \   _  _\ \   ___  \       \ \  \\|__| \  \ \   __  \   \ \  \     \ \  \ \ \  \_|/_\ \   _  _\
  \ \  \_\\ \ \  \ \  \ \  \\  \\ \  \\ \  \       \ \  \    \ \  \ \  \ \  \   \ \  \     \ \  \ \ \  \_|\ \ \  \\  \|
   \ \_______\ \__\ \__\ \__\\ _\\ \__\\ \__\       \ \__\    \ \__\ \__\ \__\   \ \__\     \ \__\ \ \_______\ \__\\ _\
    \|_______|\|__|\|__|\|__|\|__|\|__| \|__|        \|__|     \|__|\|__|\|__|    \|__|      \|__|  \|_______|\|__|\|__|
-->
HTML;

        return substr_replace( $output, '<head>' . $debug, $position, 6 );
    }

    /**
     * Return the Singleton Instance of the class.
     *
     * @return void
     */
    public static function instance() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }
}

DM_Advanced_Cache::instance();
<?php

namespace DarkMatter\AdvancedCache\Data;

use DarkMatter\Interfaces\Registerable;

class WordPressResponse extends Response implements Registerable {
	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		ob_start( [ $this, 'set_body' ] );
		add_filter( 'status_header', [ $this, 'set_status_header' ], 10, 2 );
	}

	/**
	 * Capture the body for the WordPress request.
	 *
	 * @param string $output Output for the WordPress response.
	 * @return string
	 */
	public function set_body( $output = '' ) {
		$this->body = $output;
		return $output;
	}

	/**
	 * Set the status header from the WordPress hook.
	 *
	 * @param string  $status_header HTTP status header.
	 * @param integer $status_code   HTTP status code.
	 * @return string
	 */
	public function set_status_header( $status_header = '', $status_code = 200 ) {
		$this->status_code = $status_code;
		return $status_header;
	}
}

<?php

namespace DarkMatter\AdvancedCache\Data;

use DarkMatter\Interfaces\Registerable;

class WordPressResponse extends Response implements Registerable {
	/**
	 * Register hooks.
	 *
	 * Note: used for processing a live request within WordPress. Can be ignored otherwise.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'status_header', [ $this, 'set_status_header' ], 10, 2 );
	}

	/**
	 * Set the status header from the WordPress hook.
	 *
	 * Note: used for processing a live request within WordPress. Can be ignored otherwise.
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

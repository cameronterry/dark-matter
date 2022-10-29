<?php
/**
 * Policy for putting the public facing website under maintenance, i.e. the "Apple sticky note" functionality.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\Policies;

use DarkMatter\AdvancedCache\Data\Request;
use DarkMatter\AdvancedCache\Data\ResponseEntry;
use DarkMatter\AdvancedCache\Processor\Visitor;

/**
 * Class Maintenance
 */
class Maintenance extends AbstractPolicy {
	/**
	 * Constructor
	 *
	 * @param Request $request Details on the request.
	 * @param Visitor $visitor Details on the visitor.
	 */
	public function __construct( Request $request, Visitor $visitor ) {
		$maintenance_html = \wp_cache_get( 'dark-matter-advancedcache-maintenance', 'dark-matter-fpc-responses' );

		if ( ! empty( $maintenance_html ) ) {
			$this->response = new ResponseEntry();
			$this->response->headers = [
				'Cache-Control'      => 'no-cache',
				'X-DarkMatter-Cache' => 'HIT',
			];
			$this->response->body = $maintenance_html;
		}
	}
}

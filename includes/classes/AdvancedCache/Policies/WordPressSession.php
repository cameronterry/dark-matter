<?php
/**
 * Policy to ensure that people logged in as WordPress users are not cached.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\Policies;

use DarkMatter\AdvancedCache\Data\Request;
use DarkMatter\AdvancedCache\Processor\Visitor;

/**
 * Class WordPressSession
 */
class WordPressSession extends AbstractPolicy {
	/**
	 * Constructor
	 *
	 * @param Request $request Details on the request.
	 * @param Visitor $visitor Details on the visitor.
	 */
	public function __construct( Request $request, Visitor $visitor ) {
		foreach ( $visitor->cookies as $cookie => $value ) {
			if (
				'wp' === substr( $cookie, 0, 2 )
				||
				'wordpress' === substr( $cookie, 0, 9 )
				||
				'comment_author' === substr( $cookie, 0, 14 )
			) {
				$this->stop_cache = true;
				return;
			}
		}
	}
}

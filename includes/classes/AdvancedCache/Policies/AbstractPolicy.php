<?php
/**
 * Scaffold for creating Policies for Dark Matter Advanced Cache.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\Policies;

use DarkMatter\AdvancedCache\Data\CacheEntry;
use DarkMatter\AdvancedCache\Data\Request;
use DarkMatter\AdvancedCache\Processor\Visitor;

/**
 * Class AbstractPolicy
 */
abstract class AbstractPolicy {
	/**
	 * Response.
	 *
	 * @var CacheEntry
	 */
	protected $response = '';

	/**
	 * Stop cache.
	 *
	 * @var bool
	 */
	protected $stop_cache = false;

	/**
	 * Constructor.
	 *
	 * @param Request $request Details on the request.
	 * @param Visitor $visitor Details on the visitor.
	 */
	public abstract function __construct( Request $request, Visitor $visitor );

	/**
	 * Prevent matching requests being cached.
	 *
	 * @return bool True to prevent / stop. False to continue.
	 */
	public function stop_cache() {
		return $this->stop_cache;
	}

	/**
	 * Provide a response for a policy. If a value is provided, then this will override the response and stop any
	 * processing by any matching request.
	 *
	 * @return CacheEntry
	 */
	public function response() {
		return $this->response;
	}
}

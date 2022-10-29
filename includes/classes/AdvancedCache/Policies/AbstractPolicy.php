<?php
/**
 * Scaffold for creating Policies for Dark Matter Advanced Cache.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\Policies;

use DarkMatter\AdvancedCache\Data\ResponseEntry;
use DarkMatter\AdvancedCache\Data\Request;
use DarkMatter\AdvancedCache\Processor\Visitor;

/**
 * Class AbstractPolicy
 */
abstract class AbstractPolicy {
	/**
	 * Response.
	 *
	 * @var ResponseEntry
	 */
	protected $response = '';

	/**
	 * Stop cache.
	 *
	 * @var bool
	 */
	protected $stop_cache = false;

	/**
	 * Introduce a unique variant.
	 *
	 * @var string
	 */
	protected $variant = '';

	/**
	 * Constructor.
	 *
	 * @param Request $request Details on the request.
	 * @param Visitor $visitor Details on the visitor.
	 */
	abstract public function __construct( Request $request, Visitor $visitor );

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
	 * @return ResponseEntry
	 */
	public function response() {
		return $this->response;
	}

	/**
	 * Provide a variant key that will seek and / or store different Response Entries.
	 *
	 * @return string
	 */
	public function variant() {
		return $this->variant;
	}
}

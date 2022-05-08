<?php
/**
 * Interface to help create policies for the Advanced Cache.
 *
 * @package DarkMatter\AdvancedCache
 */
namespace DarkMatter\Interfaces;

use DarkMatter\AdvancedCache\Data\Request;
use DarkMatter\AdvancedCache\Processor\Visitor;

/**
 * Interface Policy
 *
 * @since 3.0.0
 */
interface Policy {
	/**
	 * Constructor.
	 *
	 * @param Request   $request   Details on the request.
	 * @param Visitor $requester Details on the requester.
	 */
	public function __constructor( Request $request, Visitor $requester );

	/**
	 * Can the request be cached.
	 *
	 * @return boolean True to cache, false otherwise.
	 */
	public function do_cache();

	/**
	 * Maybe returns a variant key which will created a new CacheEntry for the request.
	 *
	 * @return string If conditions are met, then it returns a variant key. Not met will return a blank string.
	 */
	public function variant_key();
}

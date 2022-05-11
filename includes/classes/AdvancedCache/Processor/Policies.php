<?php
/**
 * Process any and all policies.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\Processor;

use DarkMatter\AdvancedCache\Data\Request;
use DarkMatter\Interfaces\Policy;

/**
 * Class Policies
 */
class Policies {
	/**
	 * Can cache after processing all the policies.
	 *
	 * @var bool
	 */
	private $can_cache = true;

	/**
	 * Policies.
	 *
	 * @var array
	 */
	private $policies = [];

	/**
	 * Constructor
	 *
	 * @param Request $request Details on the request.
	 * @param Visitor $visitor Details on the requester.
	 */
	public function __construct( Request $request, Visitor $visitor ) {
		/**
		 * Set the policies to be processed.
		 *
		 * @param array $policies Policies to be applied.
		 */
		$this->policies = apply_filters( 'darkmatter.advancedcache.policies', [], $request, $visitor );
	}

	/**
	 * Can the current request be cached.
	 *
	 * @return bool True can cache. False if it cannot it.
	 */
	public function can_cache() {
		return $this->can_cache;
	}

	/**
	 * Process all the policies and retrieve the variant key.
	 *
	 * @return string Variant key.
	 */
	public function get_variant() {
		$variant = [];

		foreach ( $this->policies as $policy ) {
			/**
			 * Ensure the policy has implemented the correct interface.
			 */
			if ( ! $policy instanceof Policy ) {
				continue;
			}

			/**
			 * Check if a Policy has stopped the cache.
			 */
			if ( $this->can_cache ) {
				$this->can_cache = $policy->do_cache();
				return $policy->variant_key();
			}

			$variant[] = $policy->variant_key();
		}

		$key = join( '', $variant );
		if ( empty( $key ) ) {
			return '';
		}

		return md5( $key );
	}
}

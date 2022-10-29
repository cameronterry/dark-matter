<?php
/**
 * Processor for handling Requests.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\Processor;

use DarkMatter\AdvancedCache\Data\ResponseEntry;
use DarkMatter\AdvancedCache\Data\Request;
use DarkMatter\AdvancedCache\Data\WordPressResponse;
use DarkMatter\AdvancedCache\Policies\AbstractPolicy;
use DarkMatter\AdvancedCache\Policies\Maintenance;
use DarkMatter\AdvancedCache\Policies\WordPressSession;

/**
 * Class Request
 *
 * @since 3.0.0
 */
class AdvancedCache {
	/**
	 * Details of the request.
	 *
	 * @var Request
	 */
	private $request;

	/**
	 * Variant key, as determined by processing all the policies.
	 *
	 * @var string
	 */
	private $variant = '';

	/**
	 * Details of the visitor.
	 *
	 * @var Visitor
	 */
	private $visitor;

	/**
	 * Constructor
	 */
	public function __construct() {
		/**
		 * Get the details of request and visitor.
		 */
		$this->visitor = new Visitor( $_SERVER, $_COOKIE );
		$this->request = new Request( $this->visitor->full_url );

		/**
		 * Attach the in-built policies.
		 */
		add_filter( 'darkmatter.advancedcache.policies', [ $this, 'inbuilt_policies' ], 10, 1 );

		$policy = $this->policies( $this->request, $this->visitor );
		if ( ! empty( $policy ) ) {
			/**
			 * Check if the policy is to the stop cache. If so, then we can stop execution here.
			 */
			if ( $policy->stop_cache() ) {
				header( 'X-DarkMatter-Cache: DYNAMIC' );
				header( 'X-DarkMatter-Reason: Policy-Stop' );
				return;
			}

			/**
			 * See if the policy has a response.
			 */
			$policy_entry = $policy->response();
			if ( $policy_entry instanceof ResponseEntry ) {
				$policy_entry->headers['X-DarkMatter-Reason'] = 'Policy-Response';
				$this->hit( $policy_entry );
			}
		}

		if ( $this->visitor->is_cacheable() ) {
			/**
			 * See if there is a "hit" on the cache entry. If so, then use this to serve the response and skip
			 * WordPress.
			 */
			$response_entry = $this->request->get_variant( $this->variant );

			if ( ! empty( $response_entry ) && ! $response_entry->has_expired() ) {
				$this->hit( $response_entry );
			}

			/**
			 * Here means there was no current cache entry. Therefore we do a "lookup" to generate a new cache entry
			 * with a response.
			 */
			$this->lookup( $this->request, $this->visitor->full_url );
		}
	}

	/**
	 * Handle the output of the headers for a cache hit.
	 *
	 * @param array $headers Headers to be part of the request.
	 * @return void
	 */
	public function do_headers( $headers = [] ) {
		foreach ( $headers as $name => $value ) {
			header( "{$name}: {$value}", true );
		}
	}

	/**
	 * Handle a Cache "hit".
	 *
	 * @param ResponseEntry $response_entry Cache Entry object and details.
	 *
	 * @return void
	 */
	private function hit( $response_entry ) {
		/**
		 * Handle max age for the Cache Control header. If the expiry is set to "infinite" (zero / 0), then we set a not
		 * so infinite time in the future.
		 *
		 * Otherwise, the max age will be when the cache entry expires.
		 */
		$max_age = $response_entry->expiry;
		if ( 0 === $max_age ) {
			$max_age = time() * DAY_IN_SECONDS;
		}

		$headers = array_merge(
			$response_entry->headers,
			[
				'Cache-Control'      => sprintf( 'max-age=%d', $max_age ),
				'Last-Modified'      => sprintf( '%s GMT', gmdate( 'D, d M Y H:i:s', $response_entry->lastmodified ) ),
				'X-DarkMatter-Cache' => 'HIT',
			]
		);

		$this->do_headers( $headers );
		die( Instructions::instance()->body( $response_entry->get_body( $this->request, $this->visitor ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Attach the in-built policies for processing.
	 *
	 * @param array $polices Policies as part of the filter.
	 *
	 * @return array
	 */
	public function inbuilt_policies( $polices = [] ) {
		$polices[] = WordPressSession::class;
		$polices[] = Maintenance::class;

		return $polices;
	}

	/**
	 * Handle a response.
	 *
	 * @param Request $request  Request Data object.
	 * @param string  $full_url Full URL of the current request.
	 * @return void
	 */
	private function lookup( $request, $full_url ) {
		$response = new WordPressResponse( $full_url, $this->variant, $request );
		$response->register();
	}

	/**
	 * Process the Policies for the cache and determine if any prevent caching or have a response.
	 *
	 * @param Request $request Details on the request.
	 * @param Visitor $visitor Details on the visitor.
	 *
	 * @return mixed|null If matching policy found, will return a class that inherits AbstractPolicy. Null otherwise.
	 */
	private function policies( $request, $visitor ) {
		/**
		 * An array of Policy classes which can be instantiated and then run in Advanced Cache.
		 *
		 * @param array $policies Policies to be applied.
		 * @return array
		 */
		$policies = apply_filters( 'darkmatter.advancedcache.policies', [] );
		$variants = [];

		foreach ( $policies as $policy ) {
			/**
			 * Make sure it is actually a policy.
			 */
			$obj = new $policy( $request, $visitor );
			if ( ! $obj instanceof AbstractPolicy ) {
				continue;
			}

			/**
			 * Execute the policy. If the policy instructs the cache to stop or has a response, then return it.
			 */
			if ( $obj->stop_cache() || ! empty( $obj->response() ) ) {
				return $obj;
			}

			/**
			 * Another policy type is to create variants.
			 */
			$variant = $obj->variant();
			if ( ! empty( $variant ) ) {
				$variants[] = $variant;
			}
		}

		/**
		 * Combined variants from all the processed policies and create a single key. This allows multiple policies to
		 * build up different variants based on different rules.
		 */
		if ( ! empty( $variants ) ) {
			$this->variant = md5( join( '-', $variants ) );
		}

		return null;
	}
}

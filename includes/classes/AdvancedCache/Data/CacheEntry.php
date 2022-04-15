<?php
/**
 * A Cache Entry, which contains all the headers and HTML for a response.
 *
 * @package DarkMatter
 */
namespace DarkMatter\AdvancedCache\Data;

/**
 * Class CacheEntry
 *
 * @since 3.0.0
 */
class CacheEntry implements \DarkMatter\Interfaces\Storeable {
	/**
	 * Constructor
	 */
	public function __construct( $key = '' ) {
		/**
		 * Attempt to retrieve the entry from Object Cache.
		 */
		$entry = wp_cache_get( md5( $key ), 'darkmatter-fpc-cacheentries' );

		/**
		 * Parse the entry into JSON.
		 */
		if ( ! empty( $entry ) ) {
			$this->from_json( $entry );
		}
	}

	/**
     * @inheritDoc
     */
    public function to_json() {
        // TODO: Implement to_json() method.
    }

    /**
     * @inheritDoc
     */
    public function from_json( $json = '' ) {
        // TODO: Implement from_json() method.
    }

	/**
	 * @inheritdoc
	 */
	public function save() {
		return false;
	}
}

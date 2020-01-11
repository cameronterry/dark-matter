<?php
defined( 'ABSPATH' ) || die();

/**
 * Class DM_Cache_Info
 *
 * Provides useful data in a more accessible format for a particular cache entry.
 */
class DM_Cache_Info {
    /**
     * @var DM_Request_Cache Request Cache object.
     */
    private $cache_entry = null;

    /**
     * @var array Data of the Cache Entry.
     */
    private $cache_data = [];

    /**
     * @var array All cache information for the URL and each variant.
     */
    private $data = [];

    /**
     * DM_Cache_Info constructor.
     *
     * @param string $url Request URL to check for cache data.
     */
    public function __construct( $url = '' ) {
        $this->cache_entry = new DM_Request_Cache( $url );
        $this->cache_data  = $this->cache_entry->get_data()->data();
    }

    /**
     * Returns all the available cache information.
     *
     * @return array Cache information for the URL and all variants.
     */
    public function get_all() {
        return $this->data;
    }
}
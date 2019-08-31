<?php
defined( 'ABSPATH' ) || die;

class DM_Post_Cache {
    /**
     * DM_Post_Cache constructor.
     *
     * @param string $url
     */
    public function __construct( $url = '' ) {
        foreach ( get_object_vars( $url ) as $key => $value ) {
            $this->$key = $value;
        }
    }

    /**
     * Converts this object to an array.
     *
     * @return array Object as array.
     */
    public function to_array() {
        return get_object_vars( $this );
    }
}
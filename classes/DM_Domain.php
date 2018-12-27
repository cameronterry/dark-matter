<?php

defined( 'ABSPATH' ) || die;

class DM_Domain {
    /**
     * Database ID.
     *
     * @var integer
     */
    public $id = 0;

    /**
     * Site ID. Using the old WordPress terminology of "Blog" rather than the
     * newer term of "Site".
     *
     * @var integer
     */
    public $blog_id = 0;

    /**
     * Is the Domain Primary?
     *
     * @var boolean
     */
    public $is_primary = false;

    /**
     * The FQDN (Fully Qualified Domain Name).
     *
     * @var string
     */
    public $domain = '';

    /**
     * Is the domain Active?
     *
     * @var boolean
     */
    public $active = false;

    /**
     * Is the domain to be redirect to the HTTPS version?
     *
     * @var boolean
     */
    public $is_https = false;

    /**
     * Constructor.
     *
     * @param DM_Domain|object $domain A domain object.
     */
    public function __construct( $domain ) {
        foreach ( get_object_vars( $domain ) as $key => $value ) {
            $this->$key = $value;
        }
    }
}
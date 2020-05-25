<?php
/**
 * Class DM_Domain
 *
 * @package DarkMatter
 */

defined( 'ABSPATH' ) || die;

/**
 * Class DM_Domain
 */
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

		$this->id         = (int) $this->id;
		$this->blog_id    = (int) $this->blog_id;
		$this->is_primary = (bool) $this->is_primary;
		$this->active     = (bool) $this->active;
		$this->is_https   = (bool) $this->is_https;
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

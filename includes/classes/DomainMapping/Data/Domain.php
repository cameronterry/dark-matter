<?php
/**
 * Data type for a singular domain.
 *
 * @since 2.0.0
 *
 * @package DarkMatter
 */

namespace DarkMatter\DomainMapping\Data;

/**
 * Class Domain
 *
 * Previously named `DM_Domain`.
 *
 * @since 2.0.0
 */
class Domain {
	/**
	 * Database ID.
	 *
	 * @since 2.0.0
	 *
	 * @var integer
	 */
	public $id = 0;

	/**
	 * Site ID. Using the old WordPress terminology of "Blog" rather than the
	 * newer term of "Site".
	 *
	 * @since 2.0.0
	 *
	 * @var integer
	 */
	public $blog_id = 0;

	/**
	 * Is the Domain Primary?
	 *
	 * @since 2.0.0
	 *
	 * @var boolean
	 */
	public $is_primary = false;

	/**
	 * The FQDN (Fully Qualified Domain Name).
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $domain = '';

	/**
	 * Is the domain Active?
	 *
	 * @since 2.0.0
	 *
	 * @var boolean
	 */
	public $active = false;

	/**
	 * Is the domain to be redirect to the HTTPS version?
	 *
	 * @since 2.0.0
	 *
	 * @var boolean
	 */
	public $is_https = false;

	/**
	 * Domain type: `1` is primary and secondary domains, `2` is media.
	 *
	 * @since 2.2.0
	 *
	 * @var int
	 */
	public $type = 1;

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param Domain|object $domain A domain object.
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
		$this->type       = (int) $this->type;
	}

	/**
	 * Converts this object to an array.
	 *
	 * @since 2.0.0
	 *
	 * @return array Object as array.
	 */
	public function to_array() {
		return get_object_vars( $this );
	}
}

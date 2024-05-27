<?php
/**
 * Query implementation for restricted domains.
 *
 * @package DarkMatterPlugin\Data
 */

namespace DarkMatter\DomainMapping\Data;

use DarkMatter\DomainMapping\Installer;
use DarkMatter\Helper\CustomTableQuery;

/**
 * Class RestrictedDomainQuery
 */
class RestrictedDomainQuery extends CustomTableQuery {

	/**
	 * Constructor.
	 *
	 * @param array $query Query arguments.
	 */
	public function __construct( $query ) {
		parent::__construct( $query, Installer::$restricted_domain );
	}

	/**
	 * @param $record_id
	 * @return void
	 */
	public function get_record( $record_id ) {
		// TODO: Implement get_record() method.
	}
}

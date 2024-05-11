<?php
/**
 * Domain query.
 *
 * @package DarkMatterPlugin\Data
 */

namespace DarkMatter\DomainMapping\Data;

use DarkMatter\DomainMapping\Installer;
use DarkMatter\Helper\CustomTableQuery;

/**
 * Class DomainQuery
 */
class DomainQuery extends CustomTableQuery {
	/**
	 * Constructor.
	 *
	 * @param array $query Query arguments.
	 */
	public function __construct( $query = [] ) {
		parent::__construct( $query, Installer::$domain_table );
	}

	/**
	 * Retrieve the Domain object for a record ID.
	 *
	 * @param int $record_id Record ID.
	 * @return Domain|null
	 */
	public function get_record( $record_id ) {
		$domain = $this->custom_table->get_record( $record_id );
		if ( empty( $domain ) ) {
			return null;
		}

		return new Domain( (object) $domain );
	}
}

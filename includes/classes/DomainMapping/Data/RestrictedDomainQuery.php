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
	public function __construct( $query = [] ) {
		parent::__construct( $query, Installer::$restricted_domain );
	}

	/**
	 * Retrieve the Record ID by domain.
	 *
	 * @param string $domain Domain to get ID for.
	 * @return false|int
	 */
	public function get_id_by_domain( $domain ) {
		if ( empty( $domain ) ) {
			return false;
		}

		$query = $this->query(
			[
				'domain' => $domain,
				'fields' => 'ids',
				'number' => 1,
			]
		);

		if ( empty( $query ) ) {
			return false;
		}

		return $query[0];
	}

	/**
	 * Retrieve the Restricted Domain object for a record ID.
	 *
	 * @param int $record_id Record ID.
	 * @return RestrictedDomain|null
	 */
	public function get_record( $record_id ) {
		$restricted_domain = $this->custom_table->get_record( $record_id );
		if ( empty( $restricted_domain ) ) {
			return null;
		}

		return $restricted_domain->domain;
	}
}

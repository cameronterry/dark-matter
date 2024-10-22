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
	 * Retrieve a Domain record by a domain name.
	 *
	 * @param string $domain Full domain name, excluding any protocol.
	 * @return Domain|false
	 */
	public function get_by_domain( $domain ) {
		$query = $this->query(
			[
				'active' => 'any',
				'domain' => $domain,
				'number' => 1,
			]
		);

		if ( empty( $query ) ) {
			return false;
		}

		return $query[0];
	}

	/**
	 * Get the primary domain for the blog ID.
	 *
	 * @param int $blog_id Blog to retrieve the primary domain for. Will default to the current blog ID if not supplied.
	 * @return false|Domain
	 */
	public function get_primary_domain( $blog_id = 0 ) {
		$blog_id = empty( $blog_id ) ? get_current_blog_id() : $blog_id;

		$query = $this->query(
			[
				'domain'     => $blog_id,
				'is_primary' => true,
				'number'     => 1,
			]
		);

		if ( empty( $query ) ) {
			return false;
		}

		return $query[0];
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

<?php
/**
 * Handles the management of Primary domains.
 *
 * @since 2.0.0
 *
 * @package DarkMatterPlugin\DomainMapping
 */

namespace DarkMatter\DomainMapping\Manager;

// phpcs:disable PHPCompatibility.Keywords.ForbiddenNames.unsetFound

use \DarkMatter\DomainMapping\Data;

/**
 * Class Primary
 *
 * Previously called `DarkMatter_Primary`.
 *
 * @since 2.0.0
 */
class Primary {

	/**
	 * Retrieve the Primary domain for a Site.
	 *
	 * @since 2.0.0
	 *
	 * @param int $site_id Site ID to retrieve the primary domain for.
	 * @return Data\Domain|boolean Returns the DM_Domain object on success. False otherwise.
	 */
	public function get( $site_id = 0 ) {
		$site_id = ( empty( $site_id ) ? get_current_blog_id() : $site_id );

		$query = new Data\DomainQuery(
			[
				'blog_id'    => $site_id,
				'is_primary' => true,
				'number'     => 1,
			]
		);

		if ( empty( $query->records ) ) {
			return false;
		}

		return $query->records[0];
	}

	/**
	 * Retrieve all primary domains for the Network.
	 *
	 * @since 2.0.0
	 *
	 * @return Data\Domain[] of Domain objects of the Primary domains for each Site in the Network.
	 */
	public function get_all( $count = 10, $page = 1 ) {
		$query = new Data\DomainQuery(
			[
				'number'     => $count,
				'page'       => $page,
				'is_primary' => true,
			]
		);

		return $query->records;
	}

	/**
	 * Helper function to the set the cache for the primary domain for a Site.
	 *
	 * @since 2.0.0
	 *
	 * @param  integer $site_id Site ID to set the primary domain cache for.
	 * @param  string  $domain  Domain to be stored in the cache.
	 * @return boolean True on success, false otherwise.
	 */
	public function set( $site_id = 0, $domain = '' ) {
		return $this->change( $site_id, $domain, true );
	}

	/**
	 * Unset the primary domain for a given Site. By default, will change all
	 * records with is_primary set to true.
	 *
	 * @since 2.0.0
	 *
	 * @param  integer $site_id Site ID to unset the primary domain for.
	 * @param  string  $domain  Optional. If provided, will only affect that domain's record.
	 * @param  boolean $db      Set to true to perform a database update.
	 * @return boolean          True on success. False otherwise.
	 */
	public function unset( $site_id = 0, $domain = '', $db = false ) {
		return $this->change( $site_id, $domain );
	}

	/**
	 * Helper method to change the is_primary domain.
	 *
	 * @param integer $site_id    Site ID to unset the primary domain for.
	 * @param string  $domain     Optional. If provided, will only affect that domain's record.
	 * @param boolean $is_primary Set to true to perform a database update.
	 * @return boolean True on success. False otherwise.
	 */
	private function change( $site_id = 0, $domain = '', $is_primary = false ) {
		$query = new Data\DomainQuery();

		$new_primary_domain = $query->get_by_domain( $domain );
		if ( $new_primary_domain->blog_id !== $site_id ) {
			return false;
		}

		$data = new Data\DomainMapping();
		$result = $data->update(
			[
				'id'         => $new_primary_domain->id,
				'domain'     => $new_primary_domain->domain,
				'is_primary' => $is_primary,
			],
			true
		);

		if ( is_wp_error( $result ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Change the last changed cache note.
	 *
	 * @since 2.1.8
	 *
	 * @return void
	 */
	private function update_last_changed() {
		wp_cache_set( 'last_changed', microtime(), 'dark-matter' );
	}

	/**
	 * Return the Singleton Instance of the class.
	 *
	 * @since 2.0.0
	 *
	 * @return Primary
	 */
	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}

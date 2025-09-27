<?php
/**
 * Compatibility adjustments for supporting Yoast SEO.
 *
 * @package DarkMatterPlugin
 */

namespace DarkMatterPlugin\DomainMapping\Compat;

use DarkMatterPlugin\Registerable;
use Yoast\WP\SEO\Models\Indexable;

/**
 * Class Yoast
 *
 * @since 2.1.3
 */
class Yoast implements Registerable {

	/**
	 * Very popular, but not to assume a site uses Yoast SEO (or isn't private and has no SEO).
	 *
	 * @return bool
	 */
	public function can_register() {
		return defined( 'WPSEO_VERSION' ) && class_exists( '\Yoast\WP\SEO\Models\Indexable' );
	}

	/**
	 * Correct indexables permalinks to be unmapped prior to save to the database. This works with versions 15.1+ of
	 * Yoast SEO. Version 15.1 - which contains the `wpseo_should_save_indexable` was released on 14th October 2020.
	 *
	 * @link https://github.com/Yoast/wordpress-seo/blob/15.1/src/builders/indexable-builder.php#L296
	 *
	 * @param boolean   $intend_to_save Whether the indexable is to be saved or not.
	 * @param Indexable $indexable      The indexable to be saved.
	 * @return boolean The default value of "intend to save".
	 */
	public function fix_indexable_permalinks( $intend_to_save, $indexable ) {
		/**
		 * If saving to the database, then make sure the permalink is unmapped.
		 */
		if ( $intend_to_save ) {
			$dm_url               = \DM_URL::instance();
			$indexable->permalink = $dm_url->unmap( $indexable->permalink );
		}

		return $intend_to_save;
	}

	/**
	 * Handle actions and filters for Yoast SEO compatibility.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'wpseo_should_save_indexable', [ $this, 'fix_indexable_permalinks' ], 10, 2 );
	}
}

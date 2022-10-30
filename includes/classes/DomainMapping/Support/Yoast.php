<?php
/**
 * Compatibility adjustments for supporting Yoast SEO.
 *
 * @package DarkMatter
 */

/**
 * Class DM_Yoast
 *
 * @since 2.1.3
 */
class DM_Yoast {
	/**
	 * DM_Yoast constructor.
	 */
	public function __construct() {
		if ( defined( 'WPSEO_VERSION' ) ) {
			return;
		}

		add_filter( 'wpseo_should_save_indexable', [ $this, 'fix_indexable_permalinks' ], 10, 2 );
	}

	/**
	 * Correct indexables permalinks to be unmapped prior to save to the database. This works with versions 15.1+ of
	 * Yoast SEO. Version 15.1 - which contains the `wpseo_should_save_indexable` was released on 14th October 2020.
	 *
	 * @link https://github.com/Yoast/wordpress-seo/blob/15.1/src/builders/indexable-builder.php#L296
	 *
	 * @param boolean                        $intend_to_save Whether the indexable is to be saved or not.
	 * @param \Yoast\WP\SEO\Models\Indexable $indexable The indexable to be saved.
	 * @return boolean The default value of "intend to save".
	 */
	public function fix_indexable_permalinks( $intend_to_save, $indexable ) {
		/**
		 * If saving to the database, then make sure the permalink is unmapped.
		 */
		if ( $intend_to_save ) {
			$indexable->permalink = \DarkMatter\DomainMapping\Helper::instance()->unmap( $indexable->permalink );
		}

		return $intend_to_save;
	}
}

/**
 * Only instantiate this class if Yoast SEO is in use.
 */
new DM_Yoast();

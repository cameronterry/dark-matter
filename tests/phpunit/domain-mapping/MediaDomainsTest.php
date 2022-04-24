<?php

class MediaDomainsTest extends WP_UnitTestCase {
	/**
	 * Blog ID.
	 *
	 * @var int
	 */
	private $blog_id = -1;

	/**
	 * Testing setup
	 *
	 * @return void
	 */
	public function setUp() : void {
		parent::setUp();

		/**
		 * Create a new site to test against. Maps to `wp_insert_site()`.
		 *
		 * @link https://developer.wordpress.org/reference/functions/wp_insert_site/
		 */
		$this->blog_id = $this->factory()->blog->create_object(
			[
				'domain' => 'darkmatter.test',
				'path'   => '/siteone',
			]
		);
	}



	/**
	 * Get media domains as set by an administrator.
	 *
	 * @return void
	 */
	public function test_get_media_domains_manual() {
		$media_domains = [
			'cdn1.mappeddomain1.test' => -1,
			'cdn2.mappeddomain1.test' => -1,
			'cdn3.mappeddomain1.test' => -1,
		];

		switch_to_blog( $this->blog_id );

		/**
		 * Create domains.
		 */
		foreach ( $media_domains as $domain => $id ) {
			$result = DarkMatter_Domains::instance()->add(
				$domain,
				false,
				true,
				true,
				true,
				DM_DOMAIN_TYPE_MEDIA
			);
			$this->assertNotWPError( $result, 'Media domain created.' );

			$media_domains[ $domain ] = $result->id;
		}

		/**
		 * Retrieve the domains.
		 */
		$domains = DarkMatter_Domains::instance()->get_domains_by_type();

		$expected = [];

		foreach ( $media_domains as $media_domain => $id ) {
			$expected[] = new DM_Domain(
				(object) [
					'active'     => true,
					'blog_id'    => get_current_blog_id(),
					'domain'     => $media_domain,
					'id'         => $id,
					'is_https'   => true,
					'is_primary' => false,
					'type'       => DM_DOMAIN_TYPE_MEDIA,
				]
			);
		}

		$this->assertEquals( $expected, $domains, 'Media domains set by constant.' );
	}

	/**
	 * Media domains set by the `DM_NETWORK_MEDIA` constant.
	 *
	 * @return void
	 */
	public function test_get_media_domains_constant() {
		/**
		 * This is a bit of fudge, but by making this test last ... `DM_NETWORK_MEDIA` constant does not interfere with
		 * the previous test for media domains manual.
		 */
		define( 'DM_NETWORK_MEDIA', [
			'cdn1.darkmatter.test',
		] );

		$domains = DarkMatter_Domains::instance()->get_domains_by_type();

		$expected = [];

		foreach ( DM_NETWORK_MEDIA as $media_domain ) {
			$expected[] = new DM_Domain( (object) [
				'active'     => true,
				'blog_id'    => get_current_blog_id(),
				'domain'     => $media_domain,
				'id'         => -1,
				'is_https'   => true,
				'is_primary' => false,
				'type'       => DM_DOMAIN_TYPE_MEDIA,
			] );
		}

		$this->assertEquals( $expected, $domains, 'Media domains set by constant.' );
	}
}

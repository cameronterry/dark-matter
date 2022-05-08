<?php
/**
 * Tests for the restricted domains functionality.
 *
 * @package DarkMatter
 */

/**
 * Class RestrictedDomainsTest
 */
class RestrictedDomainsTest extends WP_UnitTestCase {
	/**
	 * Blog ID used for testing.
	 *
	 * @var int
	 */
	private $blog_id = -1;

	/**
	 * Testing setup.
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
	 * Add a new restricted domain.
	 *
	 * @return void
	 */
	public function test_add_restrict_domain() {
		$domain = 'restricteddomain1.test';
		$result = DarkMatter_Restrict::instance()->add( $domain );

		$this->assertTrue( $result, 'Adding a restricted domain.' );
	}

	/**
	 * Remove a restricted domain.
	 *
	 * @return void
	 */
	public function test_remove_restrict_domain() {
		$domain = 'restricteddomain1.test';

		/**
		 * Add domain.
		 */
		$result = DarkMatter_Restrict::instance()->add( $domain );
		$this->assertTrue( $result, 'Adding a restricted domain.' );

		/**
		 * Remove domain.
		 */
		$result = DarkMatter_Restrict::instance()->delete( $domain );
		$this->assertTrue( $result, 'Removing a restricted domain.' );
	}

	/**
	 * Ensure restricted domain rules are adhered to.
	 *
	 * @return void
	 */
	public function test_restrict_domain_add_domain() {
		$domain = 'restricteddomain1.test';

		/**
		 * Add domain.
		 */
		$result = DarkMatter_Restrict::instance()->add( $domain );
		$this->assertTrue( $result, 'Adding a restricted domain.' );

		/**
		 * Attempt to add a new domain to a site and restrict.
		 */
		switch_to_blog( $this->blog_id );

		$result = DarkMatter_Domains::instance()->add( $domain );
		$this->assertWPError( $result, 'WP_Error for adding a restricted domain.' );
		$this->assertSame( 'reserved', $result->get_error_code(), 'Correct WP_Error for restricted domain.' );
	}

	/**
	 * Retrieve a number of restricted domains.
	 *
	 * @return void
	 */
	public function test_get_restricted_domains() {
		$domains = [ 'restricteddomain1.test', 'restricteddomain2.test' ];

		foreach ( $domains as $domain ) {
			$result = DarkMatter_Restrict::instance()->add( $domain );
			$this->assertTrue( $result, 'Adding a restricted domain.' );
		}

		$this->assertSame(
			$domains,
			DarkMatter_Restrict::instance()->get(),
			'Get returns all the restricted domains properly.'
		);
	}
}

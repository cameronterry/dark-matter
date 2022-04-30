<?php

class PrimaryDomainTest extends \WP_UnitTestCase {
	/**
	 * Blog ID used for testing.
	 *
	 * @var int
	 */
	private static $blog_id = -1;

	/**
	 * Holder for Domains class.
	 *
	 * @var DarkMatter_Domains|null
	 */
	private $darkmatter_domains = null;

	/**
	 * Holder for Primary class.
	 *
	 * @var DarkMatter_Primary|null
	 */
	private $darkmatter_primary = null;

	/**
	 * Testing setup.
	 *
	 * @return void
	 */
	public static function wpSetUpBeforeClass() {

		/**
		 * Create a new site to test against. Maps to `wp_insert_site()`.
		 *
		 * @link https://developer.wordpress.org/reference/functions/wp_insert_site/
		 */
		self::$blog_id = self::factory()->blog->create_object(
			[
				'domain' => 'darkmatter.test',
				'path'   => '/siteone',
			]
		);
	}

	public static function wpTearDownAfterClass() {
		wp_delete_site( self::$blog_id );
	}

	public function setUp(): void {
		parent::setUp();

		$this->darkmatter_domains = DarkMatter_Domains::instance();
		$this->darkmatter_primary = DarkMatter_Primary::instance();

		switch_to_blog( self::$blog_id );
	}

	/**
	 * Add a new primary domain.
	 *
	 * @return void
	 */
	public function test_add_primary_domain() {
		$domain = 'mappeddomain1.test';

		/**
		 * Create primary domain.
		 */
		$result = $this->darkmatter_domains->add( $domain, true, true );
		$this->assertNotWPError( $result );
	}

	/**
	 * Set a pre-existing domain to Primary.
	 *
	 * @return void
	 */
	public function test_set_primary_domain() {
		$domain = 'mappeddomain1.test';

		/**
		 * Create a new domain.
		 */
		$result = $this->darkmatter_domains->add( $domain, false, true );
		$this->assertNotWPError( $result );

		/**
		 * Set the domain to primary, ensuring DB is updated.
		 */
		$this->darkmatter_primary->set( self::$blog_id, $domain, true );

		global $wpdb;
		$data = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM `{$wpdb->base_prefix}domain_mapping` WHERE domain = %s AND blog_id = %d LIMIT 0, 1",
				$domain,
				self::$blog_id
			)
		);
		$this->assertSame( '1', $data->is_primary, 'Database update to primary.' );

		/**
		 * Ensure the domain get - which is cached - is returning the correct value.
		 */
		$result = $this->darkmatter_domains->get( $domain );
		$this->assertTrue( $result->is_primary, 'Cached update to primary.' );
	}

	/**
	 * Unset a primary domain.
	 *
	 * @return void
	 */
	public function test_unset_primary_domain() {
		$domain = 'mappeddomain1.test';

		/**
		 * Create a new domain.
		 */
		$result = $this->darkmatter_domains->add( $domain, true, true );
		$this->assertNotWPError( $result );

		/**
		 * Set the domain to primary, ensuring DB is updated.
		 */
		$this->darkmatter_primary->unset( self::$blog_id, $domain, true );

		global $wpdb;
		$data = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM `{$wpdb->base_prefix}domain_mapping` WHERE domain = %s AND blog_id = %d LIMIT 0, 1",
				$domain,
				self::$blog_id
			)
		);
		$this->assertSame( '0', $data->is_primary, 'Database update to unset primary.' );

		/**
		 * Ensure the domain get - which is cached - is returning the correct value.
		 */
		$result = $this->darkmatter_domains->get( $domain );
		$this->assertFalse( $result->is_primary, 'Cached update to unset primary.' );
	}

	/**
	 * Make sure that all domains work as expected.
	 *
	 * @return void
	 */
	public function test_get_all_primary_domains() {
		$second_blog = $this->factory()->blog->create_object(
			[
				'domain' => 'darkmatter.test',
				'path'   => '/sitetwo',
			]
		);

		$domain1 = 'mappeddomain1.test';
		$domain2 = 'mappeddomain2.test';

		$expected = [];

		/**
		 * Create and set primary domains.
		 */
		$result = $this->darkmatter_domains->add( $domain1, true, true );
		$this->assertNotWPError( $result );

		$expected[] = $result;

		switch_to_blog( $second_blog );

		/**
		 * Create and set primary domains.
		 */
		$result = $this->darkmatter_domains->add( $domain2, true, true );
		$this->assertNotWPError( $result );

		$expected[] = $result;

		/**
		 * Get all primary domains.
		 */
		switch_to_blog( self::$blog_id );

		$primaries = $this->darkmatter_primary->get_all();
		$this->assertEquals( 2, count( $primaries ), 'Two primary domains found.' );
		$this->assertEqualSets( $expected, $primaries, 'Compare to created with domains.' );
	}
}

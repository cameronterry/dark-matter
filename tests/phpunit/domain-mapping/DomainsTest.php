<?php

class DomainsTest extends \WP_UnitTestCase {
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

		switch_to_blog( $this->blog_id );
	}

	/**
	 * Adding a new domain.
	 *
	 * @return void
	 */
	public function test_add_domain() {
		$domain = 'mappeddomain1.test';
		DarkMatter_Domains::instance()->add( $domain );

		global $wpdb;
		$data = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM `{$wpdb->base_prefix}domain_mapping` WHERE domain = %s AND blog_id = %d LIMIT 0, 1",
				$domain,
				$this->blog_id
			)
		);

		$this->assertNotEmpty( $data );
	}

	/**
	 * Ensure that the same domain cannot be re-added.
	 *
	 * @return void
	 */
	public function test_add_add_again() {
		$domain = 'mappeddomain1.test';

		/**
		 * Add the domain.
		 */
		DarkMatter_Domains::instance()->add( $domain );

		/**
		 * Attempt to add the domain again.
		 */
		$error = DarkMatter_Domains::instance()->add( $domain );

		$this->assertWPError( $error );
		$this->assertSame( $error->get_error_code(), 'exists' );
	}

	/**
	 * Test removing a domain.
	 *
	 * @return void
	 */
	public function test_delete_domain() {
		$domain = 'mappeddomain1.test';

		/**
		 * Add the domain.
		 */
		DarkMatter_Domains::instance()->add( $domain );

		/**
		 * Attempt to add the domain again.
		 */
		$return = DarkMatter_Domains::instance()->delete( $domain );

		$this->assertTrue( $return );

		global $wpdb;
		$data = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM `{$wpdb->base_prefix}domain_mapping` WHERE domain = %s AND blog_id = %d LIMIT 0, 1",
				$domain,
				$this->blog_id
			)
		);

		$this->assertEmpty( $data );
	}

	/**
	 * Test finding an existing domain.
	 *
	 * @return void
	 */
	public function test_find_domain() {
		$domain = 'mappeddomain1.test';

		/**
		 * Add the domain.
		 */
		DarkMatter_Domains::instance()->add( $domain );

		/**
		 * Attempt to add the domain again.
		 */
		$return = DarkMatter_Domains::instance()->find( $domain );

		$this->assertNotFalse( $return );
		$this->assertEquals( $return->domain, $domain );
	}

	/**
	 * Test updating an existing domain.
	 *
	 * @return void
	 */
	public function test_update_domain() {
		$domain = 'mappeddomain1.test';

		/**
		 * Add the domain.
		 */
		DarkMatter_Domains::instance()->add( $domain );

		/**
		 * Make sure the update did not return a WP_Error.
		 */
		$return = DarkMatter_Domains::instance()->update( $domain, true, true );
		$this->assertNotWPError( $return );

		/**
		 * Assert the update did actually work.
		 */
		global $wpdb;
		$data = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM `{$wpdb->base_prefix}domain_mapping` WHERE domain = %s AND blog_id = %d LIMIT 0, 1",
				$domain,
				$this->blog_id
			)
		);

		$this->assertEquals( '1', $data->is_primary );
		$this->assertEquals( '1', $data->is_https );
	}

	/**
	 * Test international domains, such as Chinese, which have a system for handling non-ASCII characters.
	 *
	 * @return void
	 */
	public function test_validation_international_domains() {
		/** Chinese - Unicode - Invalid */
		$return = DarkMatter_Domains::instance()->add( 'www.例如.中国' );
		$this->assertWPError( $return, 'Chinese - Unicode - Invalid' );
		$this->assertSame( $return->get_error_code(), 'domain' );

		/** Chinese - ASCII - Valid */
		$return = DarkMatter_Domains::instance()->add( 'www.xn--fsqu6v.xn--fiqs8s' );
		$this->assertNotWPError( $return, 'Chinese - ASCII - Valid' );
	}

	/**
	 * Invalid domains and input that should not be allowed.
	 *
	 * @return void
	 */
	public function test_validation_invalid_domains() {
		/** Empty */
		$return = DarkMatter_Domains::instance()->add( '' );
		$this->assertWPError( $return );
		$this->assertSame( $return->get_error_code(), 'empty' );

		/** URI */
		$return = DarkMatter_Domains::instance()->add( 'http://example.com/' );
		$this->assertWPError( $return );
		$this->assertSame( $return->get_error_code(), 'unsure' );

		/** Domain + Path */
		$return = DarkMatter_Domains::instance()->add( 'example.com/hello-world' );
		$this->assertWPError( $return );
		$this->assertSame( $return->get_error_code(), 'unsure' );

		/** Domain + Port */
		$return = DarkMatter_Domains::instance()->add( 'example.com:443' );
		$this->assertWPError( $return );
		$this->assertSame( $return->get_error_code(), 'unsure' );

		/** DOMAIN_CURRENT_SITE */
		$return = DarkMatter_Domains::instance()->add( 'darkmatter.test' );
		$this->assertWPError( $return );
		$this->assertSame( $return->get_error_code(), 'wp-config' );

		/** Non-ASCII - i.e. emojis, etc. */
		$return = DarkMatter_Domains::instance()->add( '🐲' );
		$this->assertWPError( $return );
		$this->assertSame( $return->get_error_code(), 'domain' );

		/** Stored XSS (and curious input from an administrator one time ... ... ...) */
		$return = DarkMatter_Domains::instance()->add( '<script>( function ( $ ) { $.ready( () => { console.log( \'hello world\' ); } ); } )( window.jQuery )</script>' );
		$this->assertWPError( $return );
		$this->assertSame( $return->get_error_code(), 'unsure' );
	}

	/**
	 * Test valid domains.
	 *
	 * @return void
	 */
	public function test_validation_valid_domains() {
		/**
		 * Valid domains
		 * =============
		 */

		/** Localhost */
		$return = DarkMatter_Domains::instance()->add( 'localhost' );
		$this->assertNotWPError( $return );

		/** Example domain */
		$return = DarkMatter_Domains::instance()->add( 'example.com' );
		$this->assertNotWPError( $return );

		/** Example sub-domain */
		$return = DarkMatter_Domains::instance()->add( 'www.example.com' );
		$this->assertNotWPError( $return );

		/** Atypical test domain. */
		$return = DarkMatter_Domains::instance()->add( 'development.test' );
		$this->assertNotWPError( $return );
	}
}

<?php
/**
 * Tests for ensure domain mapping is applied - and, importantly, not applied - for particular functionality.
 *
 * @package DarkMatter
 */

/**
 * Class MappingDomainsTest
 */
class MappingDomainsTest extends \WP_UnitTestCase {
	/**
	 * Attachment ID.
	 *
	 * @var int
	 */
	private $attachment = -1;

	/**
	 * Blog ID.
	 *
	 * @var int
	 */
	private $blog_id = -1;

	/**
	 * Media domain.
	 *
	 * @var string
	 */
	private $media_domain = 'cdn.example.test';

	/**
	 * Post
	 *
	 * @var WP_Post|null
	 */
	private $post = null;

	/**
	 * Media domain.
	 *
	 * @var string
	 */
	private $primary_domain = 'mappeddomain1.test';

	/**
	 * Setup.
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

		/**
		 * Add domains to the new site.
		 */
		DarkMatter_Domains::instance()->add(
			$this->primary_domain,
			true,
			true
		);

		/**
		 * Create a post to use. Maps to `wp_insert_post()`.
		 *
		 * @link https://developer.wordpress.org/reference/functions/wp_insert_post/
		 */
		$this->post       = $this->factory()->post->create_and_get();
		$this->attachment = $this->factory()->attachment->create_upload_object(
			DARKMATTER_PHPUNIT_DIR . '/includes/images/wordpress-logo.png',
			$this->post->ID
		);

		/**
		 * Set the attachment to be the feature image.
		 */
		set_post_thumbnail( $this->post, $this->attachment );
	}

	/**
	 * Ensure admin URL uses the unmapped domain.
	 *
	 * @return void
	 */
	public function test_admin_url() {
		$this->assertEquals(
			get_admin_url( null, '/' ),
			sprintf( 'https://%1$s/siteone/wp-admin/', WP_TESTS_DOMAIN )
		);
	}

	/**
	 * Ensure the generic get attachment URL returns a Media Domain.
	 *
	 * @return void
	 */
	public function test_attachment_src() {
		$url = wp_get_attachment_image_url( $this->attachment );
		$pos = stripos(
			$url,
			sprintf( 'https://%1$s/', $this->media_domain )
		);

		$this->assertNotFalse( $pos, '' );
	}

	/**
	 * A site with a primary domain will modify the Home URL.
	 *
	 * @return void
	 */
	public function test_home_url() {
		$this->assertEquals(
			get_home_url( null, '/' ),
			sprintf( 'https://%1$s/', $this->primary_domain )
		);
	}

	/**
	 * Ensure the feature image uses a Media Domain.
	 *
	 * @return void
	 */
	public function test_feature_image() {
		$html = get_the_post_thumbnail( $this->post->ID );
		$pos  = stripos(
			$html,
			sprintf( 'https://%1$s/', $this->media_domain )
		);

		$this->assertNotFalse( $pos, '' );
	}

	/**
	 * Ensure the login URL goes to the admin domain (unmapped).
	 *
	 * @return void
	 */
	public function test_login_url() {
		$this->assertEquals(
			wp_login_url(),
			sprintf( 'https://%1$s/siteone/wp-login.php', WP_TESTS_DOMAIN ),
			'Login URL'
		);
	}

	/**
	 * Ensure the logout URL goes to the admin domain (unmapped).
	 *
	 * @return void
	 */
	public function test_logout_url() {
		$url = wp_logout_url();

		/**
		 * We use `stripos()` as the logout action contains a nonce in the query string.
		 */
		$pos = stripos(
			$url,
			sprintf( 'https://%1$s/siteone/wp-login.php?action=logout', WP_TESTS_DOMAIN )
		);

		$this->assertNotFalse( $pos, 'Logout URL.' );
	}

	/**
	 * Ensure the REST URL to ensure it is .
	 *
	 * @return void
	 */
	public function test_rest_url() {
		$this->assertEquals(
			/**
			 * Ensure the REST URL is HTTPS (it gets confused because it checks a number of `$_SERVER` variables).
			 */
			set_url_scheme( get_rest_url(), 'https' ),
			sprintf( 'https://%1$s/siteone/wp-json/', $this->primary_domain ),
			'REST API URL'
		);
	}
}

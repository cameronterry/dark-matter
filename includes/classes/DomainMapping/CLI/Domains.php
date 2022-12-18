<?php
/**
 * CLI for managing domains.
 *
 * @since 2.0.0
 *
 * @package DarkMatterPlugin\DomainMapping
 */

namespace DarkMatter\DomainMapping\CLI;

use DarkMatter\DomainMapping\Manager;
use WP_CLI;
use WP_CLI_Command;

// phpcs:disable PHPCompatibility.Keywords.ForbiddenNames.listFound -- Changing CLI for list would introduce backward compatibility (2.x.x) problems for pre-existing users.

/**
 * Class Domains
 *
 * Previously called `DarkMatter_Domain_CLI`.
 *
 * @since 2.0.0
 */
class Domains extends WP_CLI_Command {
	/**
	 * Add a domain to a site on the WordPress Network.
	 *
	 * ### OPTIONS
	 *
	 * <domain>
	 * : The domain you wish to add.
	 *
	 * [--disable]
	 * : Allows you to add a domain, primary or secondary, to the Site without
	 * it being used immediately.
	 *
	 * [--force]
	 * : Force Dark Matter to add the domain. This is required if you wish to
	 * remove a Primary domain from a Site.
	 *
	 * [--https]
	 * : Sets the protocol to be HTTPS. This is only needed when used with the --primary flag and is ignored otherwise.
	 *
	 * [--primary]
	 * : Sets the domain to be the primary domain for the Site, the one which visitors will be redirected to.
	 *
	 * [--secondary]
	 * : Sets the domain to be a secondary domain for the Site. Visitors will be redirected from this domain to the primary.
	 *
	 * [--type]
	 * : Choose the type of domain. Useful for creating "media" domains. Defaults to "main".
	 *
	 * ### EXAMPLES
	 * Set the primary domain and set the protocol to HTTPS.
	 *
	 *      wp --url="sites.my.com/siteone" darkmatter domain add www.primarydomain.com --primary --https
	 *
	 * Set a media domain for a site.
	 *
	 *      wp --url="sites.my.com/sitefifteen" darkmatter domain add fifteen.mycdn.com --type=media
	 *
	 * @since 2.0.0
	 *
	 * @param array $args CLI args.
	 * @param array $assoc_args CLI args maintaining the flag names from the terminal.
	 */
	public function add( $args, $assoc_args ) {
		if ( empty( $args[0] ) ) {
			WP_CLI::error( __( 'Please include a fully qualified domain name to be added.', 'dark-matter' ) );
		}

		$fqdn = $args[0];

		$opts = wp_parse_args(
			$assoc_args,
			[
				'disable' => false,
				'force'   => false,
				'https'   => true,
				'primary' => false,
				'type'    => 'main',
			]
		);

		$type = $this->check_type_opt( $opts['type'] );

		/**
		 * Add the domain.
		 */
		$db     = Manager\Domain::instance();
		$result = $db->add( $fqdn, $opts['primary'], $opts['https'], $opts['force'], ! $opts['disable'], $type );

		if ( is_wp_error( $result ) ) {
			$error_msg = $result->get_error_message();

			if ( 'primary' === $result->get_error_code() ) {
				$error_msg = __( 'You cannot add this domain as the primary domain without using the --force flag.', 'dark-matter' );
			}

			WP_CLI::error( $error_msg );
		}

		WP_CLI::success( $fqdn . __( ': was added.', 'dark-matter' ) );
	}

	/**
	 * Checks to ensure the value of type is valid and useable.
	 *
	 * @param string $type Type value to be checked.
	 * @return integer Domain type.
	 *
	 * @since 2.2.0
	 */
	private function check_type_opt( $type = '' ) {
		/**
		 * Handle the Media flag.
		 */
		$domain_types = [
			'main'  => DM_DOMAIN_TYPE_MAIN,
			'media' => DM_DOMAIN_TYPE_MEDIA,
		];

		if ( array_key_exists( strtolower( $type ), $domain_types ) ) {
			return $domain_types[ $type ];
		}

		return DM_DOMAIN_TYPE_MAIN;
	}

	/**
	 * Include this CLI amongst the others.
	 *
	 * @return void
	 */
	public static function define() {
		WP_CLI::add_command( 'darkmatter domain', self::class );
	}

	/**
	 * List a domain for the current Site. If the the URL is omitted and the
	 * command is run on the root Site, it will list all domains available for
	 * the whole network.
	 *
	 * ### OPTIONS
	 *
	 * [--format]
	 * : Determine which format that should be returned. Defaults to "table" and
	 * accepts "json", "csv", "yaml", and "count".
	 *
	 * [--primary]
	 * : Filter the results to return only the Primary domains. This will ignore
	 * the --url parameter.
	 *
	 * ### EXAMPLES
	 * List all domains for a specific Site.
	 *
	 *      wp --url="sites.my.com/siteone" darkmatter domain list
	 *
	 * Get all domains for a specific Site in JSON format.
	 *
	 *      wp --url="sites.my.com/siteone" darkmatter domain list --format=json
	 *
	 * List all domains for all Sites.
	 *
	 *      wp darkmatter domain list
	 *
	 * @since 2.0.0
	 *
	 * @param array $args CLI args.
	 * @param array $assoc_args CLI args maintaining the flag names from the terminal.
	 */
	public function list( $args, $assoc_args ) {
		/**
		 * Handle and validate the format flag if provided.
		 */
		$opts = wp_parse_args(
			$assoc_args,
			[
				'format'  => 'table',
				'primary' => false,
			]
		);

		if ( ! in_array( $opts['format'], [ 'table', 'json', 'csv', 'yaml', 'count' ] ) ) {
			$opts['format'] = 'table';
		}

		if ( $opts['primary'] ) {
			$db      = Manager\Primary::instance();
			$domains = $db->get_all();
		} else {
			/**
			 * Retrieve the current Blog ID. However this will be set to null if
			 * this is the root Site to retrieve all domains.
			 */
			$site_id = get_current_blog_id();

			if ( is_main_site() ) {
				$site_id = null;
			}

			$db      = Manager\Domain::instance();
			$domains = $db->get_domains( $site_id );
		}

		/**
		 * Filter out and format the columns and values appropriately.
		 */
		$domains = array_map(
			function ( $domain ) {
				$no_val  = __( 'No', 'dark-matter' );
				$yes_val = __( 'Yes', 'dark-matter' );

				$columns = [
					'F.Q.D.N.' => $domain->domain,
					'Primary'  => ( $domain->is_primary ? $yes_val : $no_val ),
					'Protocol' => ( $domain->is_https ? 'HTTPS' : 'HTTP' ),
					'Active'   => ( $domain->active ? $yes_val : $no_val ),
					'Type'     => ( DM_DOMAIN_TYPE_MEDIA === $domain->type ? 'Media' : 'Main' ),
				];

				/**
				 * If the query is the root Site and we are displaying all domains,
				 * then we retrieve and include the Site Name.
				 */
				$site = get_site( $domain->blog_id );

				if ( empty( $site ) ) {
					$columns['Site'] = __( 'Unknown.', 'dark-matter' );
				} else {
					$columns['Site'] = $site->blogname;
				}

				return $columns;
			},
			$domains
		);

		/**
		 * Determine which headers to use for the Display.
		 */
		$display = [
			'F.Q.D.N.',
			'Primary',
			'Protocol',
			'Active',
			'Type',
		];

		if ( is_main_site() ) {
			$display = [
				'F.Q.D.N.',
				'Site',
				'Primary',
				'Protocol',
				'Active',
				'Type',
			];
		}

		WP_CLI\Utils\format_items( $opts['format'], $domains, $display );
	}

	/**
	 * Remove a specific domain on a Site on the WordPress Network.
	 *
	 * ### OPTIONS
	 *
	 * <domain>
	 * : The domain you wish to remove.
	 *
	 * [--force]
	 * : Force Dark Matter to remove the domain. This is required if you wish to
	 * remove a Primary domain from a Site.
	 *
	 * ### EXAMPLES
	 * Remove a domain from a Site.
	 *
	 *      wp --url="sites.my.com/siteone" darkmatter domain remove www.primarydomain.com
	 *
	 * Remove a primary domain from a Site. Please note; this ***WILL NOT*** set
	 * another domain to replace the Primary. You must set this using either the
	 * add or set commands.
	 *
	 *      wp --url="sites.my.com/siteone" darkmatter domain remove www.primarydomain.com --force
	 *
	 * @since 2.0.0
	 *
	 * @param array $args CLI args.
	 * @param array $assoc_args CLI args maintaining the flag names from the terminal.
	 */
	public function remove( $args, $assoc_args ) {
		if ( empty( $args[0] ) ) {
			WP_CLI::error( __( 'Please include a fully qualified domain name to be removed.', 'dark-matter' ) );
		}

		$fqdn = $args[0];

		$opts = wp_parse_args(
			$assoc_args,
			[
				'force' => false,
			]
		);

		$db = Manager\Domain::instance();

		/**
		 * Remove the domain.
		 */
		$result = $db->delete( $fqdn, $opts['force'] );

		if ( is_wp_error( $result ) ) {
			$error_msg = $result->get_error_message();

			if ( 'primary' === $result->get_error_code() ) {
				$error_msg = __( 'You cannot delete a primary domain. Use --force flag if you really want to and know what you are doing.', 'dark-matter' );
			}

			WP_CLI::error( $error_msg );
		}

		WP_CLI::success( $fqdn . __( ': has been removed.', 'dark-matter' ) );
	}

	/**
	 * Update the flags for a specific domain on a Site on the WordPress Network.
	 *
	 * ### OPTIONS
	 *
	 * <domain>
	 * : The domain you wish to update.
	 *
	 * [--enable]
	 * : Enable the domain on the Site.
	 *
	 * [--disable]
	 * : Disable the domain on the Site.
	 *
	 * [--force]
	 * : Force Dark Matter to update the domain.
	 *
	 * [--use-http]
	 * : Set the protocol to be HTTP.
	 *
	 * [--use-https]
	 * : Set the protocol to be HTTPS.
	 *
	 * [--primary]
	 * : Set the domain to be the primary domain for the Site, the one which
	 * visitors will be redirected to. If a primary domain is already set, then
	 * you must use the --force flag to perform the update.
	 *
	 * [--secondary]
	 * : Set the domain to be a secondary domain for the Site. Visitors will be
	 * redirected from this domain to the primary.
	 *
	 * [--type]
	 * : Choose the type of domain. Useful for creating "media" domains. Defaults to "main".
	 *
	 * ### EXAMPLES
	 * Set the primary domain and set the protocol to HTTPS.
	 *
	 *      wp --url="sites.my.com/siteone" darkmatter domain set www.primarydomain.com --primary
	 *      wp --url="sites.my.com/siteone" darkmatter domain set www.secondarydomain.com --secondary
	 *
	 * Convert a secondary domain into a media domain. Useful for when repurposing an old domain for use a CDN for media
	 * assets.
	 *
	 *      wp --url="sites.my.com/siteone" darkmatter domain set www.secondarydomain.com --type=media
	 *
	 * Convert a Media domain to a main domain. This is useful in scenarios when a media domain is redundant and to
	 * ensure it redirects to the website.
	 *
	 *      wp --url="sites.my.com/siteone" darkmatter domain set one.mycdntest.com --type=main --secondary
	 *
	 * @since 2.0.0
	 *
	 * @param array $args CLI args.
	 * @param array $assoc_args CLI args maintaining the flag names from the terminal.
	 */
	public function set( $args, $assoc_args ) {
		if ( empty( $args[0] ) ) {
			WP_CLI::error( __( 'Please include a fully qualified domain name to be removed.', 'dark-matter' ) );
		}

		$fqdn = $args[0];

		$db            = Manager\Domain::instance();
		$domain_before = $db->get( $fqdn );

		$opts = wp_parse_args(
			$assoc_args,
			[
				'disable'   => false,
				'enable'    => false,
				'force'     => false,
				'use-http'  => null,
				'use-https' => true,
				'primary'   => null,
				'secondary' => null,
			]
		);

		/**
		 * Ensure that contradicting options are not being supplied.
		 */
		if ( $opts['use-http'] && $opts['use-https'] ) {
			WP_CLI::error( __( 'A domain cannot be both HTTP and HTTPS.', 'dark-matter' ) );
		}

		if ( $opts['primary'] && $opts['secondary'] ) {
			WP_CLI::error( __( 'A domain cannot be both primary and secondary.', 'dark-matter' ) );
		}

		if ( $opts['enable'] && $opts['disable'] ) {
			WP_CLI::error( __( 'A domain cannot be both enabled and disabled.', 'dark-matter' ) );
		}

		/**
		 * Determine if we are switching between HTTP and HTTPS.
		 */
		$is_https = $opts['use-https'];

		if ( $opts['use-http'] ) {
			$is_https = false;
		}

		/**
		 * Determine if we are switching between primary and secondary.
		 */
		$is_primary = $opts['primary'];

		if ( $opts['secondary'] ) {
			$is_primary = false;
		}

		/**
		 * Determine if we are switching between enabled and disabled.
		 */
		$active = $domain_before->active;

		if ( $opts['enable'] ) {
			$active = true;
		}

		if ( $opts['disable'] ) {
			$active = false;
		}

		/**
		 * If the type is specified, then validate it to ensure it is correct.
		 */
		$type = null;
		if ( ! empty( $opts['type'] ) ) {
			$type = $this->check_type_opt( $opts['type'] );
		}

		/**
		 * Update the records.
		 */
		$result = $db->update( $fqdn, $is_primary, $is_https, $opts['force'], $active, $type );

		/**
		 * Handle the output for errors and success.
		 */
		if ( is_wp_error( $result ) ) {
			$error_msg = $result->get_error_message();

			if ( 'primary' === $result->get_error_code() ) {
				$error_msg = __( 'You cannot modify the primary domain. Use --force flag if you really want to and know what you are doing.', 'dark-matter' );
			}

			WP_CLI::error( $error_msg );
		}

		WP_CLI::success( $fqdn . __( ': successfully updated.', 'dark-matter' ) );
	}
}

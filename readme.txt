=== Dark Matter ===
Contributors: cameronterry
Tags: domain mapping, multisite
Requires at least: 5.0
Requires PHP: 7.0.0
Tested up to: 6.1
Stable tag: 2.3.4
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WARNING: Dark Matter is a highly opinionated domain mapping plugin for WordPress
Networks, designed to work out of the box as-is with minimal setup.

== Description ==

Dark Matter is a highly opinionated domain mapping plugin for WordPress
Networks, designed to work out of the box as-is with no setup.

Unlike other plugins such as Donncha's "WordPress MU Domain Mapping" and WPMU
Dev's premium domain mapping plugin, Dark Matter offers virtually no options.

It is designed specifically to work as follows;

* To have separate domain for /wp-admin/ and for the front-end websites.
* Provide per blog administrator users the ability to map a domain for their website.
* Ensure that the WordPress Admin Bar is visible on all mapped domains using a basic single sign-on implementation.

== Installation ==

**Please note;** these installation steps are primarily for brand new Networks
and not for migrating from another domain mapping plugin.

= New / Standard Installation =

1. Add the plugin files to the /wp-content/plugins/ directory like any other plugin.
2. Go to the Network Admin > Plugins area and click "Network Activate".
3. Copy the /root/wp-content/plugins/dark-matter/sunrise.php to /root/wp-content/sunrise.php
4. Modify wp-config.php to include - define( 'SUNRISE', 'on' );

= Web Server steps =

On your web server software (e.g. Apache HTTPD or Nginx), ensuring that your
configuration permits multiple domains and / or the domains you specifically
want to map to your WordPress multisite.

Go to (URL) to get more information and examples of how to configure your Apache
HTTPD or Nginx setup.

== Frequently Asked Questions ==

= Why is Dark Matter so opinionated and has so few options? =

"With great power, comes great responsibility." Dark Matter is designed to
deliver a single Network with a very specific configuration. The setups that it
serves currently are installations requiring the Admin area of WordPress to be
on a separate domain from the main websites and for users to be able to navigate
cleanly between them.

Rationale? These specific installations require a redundancy which enables the
Admin area of WordPress to remain functional and usable in the event of a
website failure. Having separate DNS settings for each provides an easy
(although depending on circumstances, maybe not for all scenarios, the best) way
to map front-end and admin-end to separate infrastructure.

= Does my multisite need to be subdirectory or sub-domains? =

Dark Matter is designed and tested to work with both a sub-directory and a
sub-domain WordPress multisite.

= Does Dark Matter scale / work with large traffic websites? =

Dark Matter is currently in use on a single WordPress Network (sub-directory)
handling between 10 million and 15 million page views per month (according to
Google Analytics) with over 60 websites.

== Screenshots ==

1. Admin interface for mapping Domains to a specific website.

== Changelog ==

= 2.3.4 =

* Fixed an issue where assets such as CSS and JavaScript were mapped on login/register pages, even if the URL is on the admin domain. This was found in the forthcoming 3.0.0 release.
* PHP 8.1 compatibility fixes and tweaks. Dark Matter now supports versions 7.0 to 8.1, in line with WordPress Core.
* Plugin development changes:
  * Added GitHub Dependency Review action, which runs on every pull request. [More information can be found on GitHub here](https://docs.github.com/en/code-security/supply-chain-security/understanding-your-software-supply-chain/about-dependency-review).
  * Temporarily disabled the Snyk check due to a peculiar error.
* Composer and NPM dependencies updated.
* Tested upto WordPress 6.1

= 2.3.3 =

* Updated the dependencies for Composer and Node.
* Added version bump dependency to aid in future releases of Dark Matter.
* Tested upto WordPress 6.0.3

= 2.3.2 =

* Fixed an uncaught issue with the way the cached domain is cast into an object.

= 2.3.1 =

* Fixed a major performance issue introduced in 2.3.0 for excessive database calls for primary domains.
  * This was caused by a malformed cache set for primary domains, after it was moved to resolve an issue previously where it would update only the database when a new primary domain was set.
  * Also removed an old call to set a primary domain, which is no longer needed and was causing irrelevant `UPDATE` SQL queries (it was essentially update the values to exactly what they were already).
* Moved the primary domain cache set when retrieving a domain to slightly later in the process.
* Atypical installations should not need to flush the cache. However, you may need to use WP CLI `wp cache flush` - or equivalent - after upgrading.

= 2.3.0 =

* Added `switch_to_blog()` support for Media Domains.
  * When using the various attachment functions within the `switch_to_blog()` context, it will now respect media domain settings on a per site basis.
  * Setting media domains through the `DM_NETWORK_MEDIA` constant now works more fully.
  * It is now possible to controlled media domains through the `network_media` property on `DarkMatter_Domains`, to enable more sophisticated logic in code.
* Tweaked the way mapped domains are detected to better support scenarios involving `switch_to_blog()`.
  * No longer relies solely on the `DOMAIN_MAPPING` constant, set when a request is processed through a primary domain.
  * Essentially, if the website handling the request is being viewed through its primary domain, then URLs within a `switch_to_blog()` context will be mapped if applicable (i.e. the blog switched to has an active primary domain).
  * And vice-versa - if a request is through the admin domain ("unmapped"), then URLs in the `switch_to_blog()` context will be unmapped as well (to prevent cross-domain compatibility issues / warnings in browsers).
* Fixed an issue where setting and unsetting the Primary Domain would update the database only, and not the cache.
  * `DarkMatter_Domains` now handles the cache state for both primary and general domain caches.
  * Also removes some duplicate database update logic.
  * The third parameter of action hooks `darkmatter_primary_set` and `darkmatter_primary_unset` is now deprecated. It is always `true` as the database is always updated now, therefore it is redundant.
* Domains are now ordered alphabetically - A to Z - when returned by `get_domains_by_type()`.
* Fixed a typo preventing the cache retrieval for Restricted Domains working properly.
* Fixed a malformed header for the 2.2.3 release in readme.txt file.
* First iteration of unit tests added to the project to improve quality assurance of this release and future releases.
  * Uses PHP Unit and based on the setup provided by the WP CLI scaffold.
  * Issues / fixes in this release were found by the new unit tests.
  * Unit tests are not included with the release version of the plugin, and are accessible via Github repository: https://github.com/cameronterry/dark-matter/
* Updated NPM dependencies to their latest versions.
* Tested with WordPress 6.0 RC1.
* We have incorporated [Snyk](https://snyk.io/) scanning for dependencies in Composer and NPM.

= 2.2.3 =

* Updated Composer and NPM dependencies.
  * No change in any logic code.
  * Some lint fixes in JavaScript and PHP files.
  * Switched `eslint-loader` for `eslint-webpack-plugin`.
* PHP CodeSniffer set to test PHP 8.0 to ensure compatibility and support going forward.
* Tested up to WordPress 5.9.3.

= 2.2.2 =

* Updated Composer and NPM dependencies. No code changes made to CSS and JS files.
* Tested with WordPress 5.9 "Joséphine".
* Checked for basic compatibility with Rank Math 1.0.81 and Yoast SEO 18.

= 2.2.1 =

* Fixes an issue caused by a discrepancy between eslint setup.
* This caused the build script to fail and was missed in 2.2.0, meaning the UI broke at the final deploy stage.
* Apologies for the inconvenience.

= 2.2.0 =

* New Features:
  * Say hello to "Media domains"; a new type of domain to Dark Matter that can be used to serve media assets from a different domain name.
    * Useful for separating infrastructure which is used for static assets from servers powering PHP requests.
    * Or powering images through a dedicated / dynamic service such as [Tachyon](https://github.com/humanmade/tachyon).
    * Can be hard-set using an array of domains in a constant called, `DM_NETWORK_MEDIA`.
    * Supports all extensions that are supported by the Media Library. So if you add SVG upload support, "Media domains" will support it too.
    * Updating the existing CLIs to support domain types and management of "Media domains".
  * WordPress' plugin screen will now notify you of future releases and to update.
    * Added support for WordPress auto-update functionality.
    * Releases after 2.2.0, you will be able to update Dark Matter through WP CLI or admin interface (depending on your setup / file permissions).
    * Servers are renewable powered!
* Bug fixes and maintenance:
  * Updated the `readme.md` file to include CLI examples for Media Domains.
  * Updated the `readme.md` file to include notes on two constants that can be used with Dark Matter for disabling SSO and configuring Media Domains for an entire Multisite.
  * Improved the domain validation when adding new domains.
  * Added plugin header comment block to the `sunrise.php` dropin.
    * There is no code and / or logic changes, however you will need to run `wp darkmatter dropin update --force`.
    * This will provide better information on WordPress plugins screen, clearly identifying Dark Matter's sunrise dropin from others.
    * This also improves diagnostic data for other plugins, such as Redis Cache / `wp redis status`.
  * Tweaked the release shell script to better support [wp-update-server](https://github.com/YahnisElsts/wp-update-server).
  * Updated composer dependencies and support for Composer 2.2.x version.
  * Updated npm dependencies, excluding eslint.

= 2.1.9 =

* Fixed an issue with Webpack that prevents the admin UI working when `SCRIPT_DEBUG` is used.
* Unlikely to experience issue except for staging and local developments.
* Problem spotted during development of the 2.2.0 release.

= 2.1.8 =

* Added support for `wp_cache_get_last_changed( 'dark-matter' )`, to quickly and easily detect changes to domain mapping.
  * This change was made to `DarkMatter_Domains` and `DarkMatter_Primary`.
  * This is useful as a quick way of determining when changes to domain mapping has occurred.
  * Will update for any domain changes on the WordPress Network.
* Cache is now updated before the `darkmatter_primary_unset` hook is fired, which is more consistent with other cache update / hooks.
* Tested Dark Matter with PHP 8.0.
* Updated Composer and Node dependencies to newer versions.
* Removed Husky and pre-commit linting due to on-going issues.

= 2.1.7 =

* Fixed an issue preventing continuous builds failing if using a not Git process, due to Husky throwing an error.
* Updated Composer and Node dependencies to newer versions.
* Upped WordPress Core support to 5.8.

= 2.1.6 =

* Change: editors will now see mapped URLs when inserting links to posts. Also, editor will now load with mapped URLs and this will be unmapped on save.
  * This fixes a really annoying issue for editors, who would could see unmapped links in Classic and Block Editors.
  * Usually occurs around linking to another piece of content like a Page, Post or Term.
  * Resolves a problem with SEO plugins such as Rank Math or Yoast SEO which would get confused between the admin and primary domains for counting internal links.
* Properly resolves an issue of unmapping the primary domain in content on Save Post, which would disengage on POST requests (Classic Editor basically).
  * This issue was introduced in 2.1.4 - sorry!
* Fixed a problem where WordPress' validation of a "safe" URL didn't always work. This mostly affects the WP HTTP API and `wp_safe_redirect()`.
* "Internal" REST API requests - conducted through `rest_do_request()` - are now treated identically to "external" REST API requests in Dark Matter.
* Switched out `webpack-fix-style-only-entries` for `webpack-remove-empty-scripts` for Webpack 5 compatibility in the build process.
* Upped WordPress Core support to 5.7.2.

= 2.1.5 =

* Fixed a PHP notice for certain requests in the redirect logic.
* Fixed an issue with embedding a post from the same site would not work in some circumstances.
  * Essentially the "mapping" process would run too late and WordPress would attempt to embed by the admin domain rather than the primary mapped domain (the domain used to visit the site).
  * The "mapping" process now runs twice; once before the oEmbed processes the post content. And again much later to ensure any dynamic blocks or other plugins have their output mapped to the primary domain.
  * Note: when using domain mapping, it is normal to see a single embed appear twice in post meta with Dark Matter. One embed for the admin domain / editors and another for the primary domain / visitors.

= 2.1.4 =

* Tweaked some conditional checks to code which is more performant. The logic is identical to before, just utilising a slightly different mechanism to achieve it. This change was applied to:
  * Detecting and executing redirects, such as those from secondary domains to the primary domain as well as login and admin pages to the admin domain.
  * A check for mapping `admin-ajax.php` and `admin-post.php` appropriately on the primary domain.
  * URL mapping on the admin side.
  * URL mapping for Home URL and Site URL options.
* Developing (for working with Dark Matter) updates:
  * Upgrade Composer / NPM dependencies to latest versions.
  * Upgraded Lodash, a dependency of some packages in use, to a new version.
  * Migrated Husky from v4 to v6 - missed in the previous 2.1.3 release.
  * Fixed the Husky integration for the pre-commit hooks ... except it doesn't work in Gitkraken (a git GUI client).
  * Fixed some minor PHPCS issues around the DM_Yoast class that was missed due to the aforementioned Husky / pre-commit issue.
* Fixed a PHPCS warning when checking AJAX action in domain mapping for admin area.

= 2.1.3 =

* Ensures that the post content stores URLs in their "unmapped" form.
  * This fixes issues with determining "internal links" by SEO plugins such as Yoast and Rank Math.
  * Part of Dark Matter's goal of being as compatible as possible if removed.
  * Also compensates for an issue where Gutenberg can add mapped domains to the post content through the inline link controls.
* A new fix for Yoast SEO indexables to ensure it stores unmapped domains.
* Developer notes (this does not alter the functionality of the plugin):
  * Ensured compatibility with Composer 2 for coding standards.
  * Updated NPM packages to the latest versions where applicable.
  * Updated Webpack configuration as part of the update.
* Rank Math SEO plugin added to the list of compatibility checks.
* Fixed the copyright year in the license.txt file.
* Tested with WordPress 5.7.

= 2.1.2 =

* Added two new filters to allow the override of permission levels.
  * Domain management with `dark_matter_domain_permission`.
  * Restricted domain management with `dark_matter_restricted_permission`.
* Admin page now uses the same permission check as the REST API endpoints which power it.
* Fixed the "View [Post Type]" links in Block Editor / Gutenberg by ensuring the home_url is mapped on REST API calls.
* Fixed an issue where `get_preview_post_link()` used the mapped domain when called within REST API request.
  * This should also fix a randomly occurring issue where "Sorry, you are not allowed to preview drafts" shows after clicking Preview.
  * For note: due to a quirk of Block Editor / Gutenberg, some times the "Preview in new tab" will show the mapped domain on hover but clicking will now load on admin domain.
* Fixed the delete notice to use the domain name. Previously it just said " has been deleted." with little context.
* Removed the warning from the Github readme.

= 2.1.1 =

* Tested with WordPress 5.5's introduction of sitemaps (see https://make.wordpress.org/core/2020/07/22/new-xml-sitemaps-functionality-in-wordpress-5-5/).
* Removed and reintroduced the `home_url` hook in certain situations for mapping certain admin links to the primary domain.
  * This fixes an issue where Yoast 14.0+ was storing mapped and unmapped URLs in the indexables table (see https://yoast.com/indexables/).
  * This occurred when Yoast was pre-emptively populating the indexables when navigating the admin area.
* Updated NPM dependencies.
* Changed `npm run start` to build both min and non-min assets. (That said, best to keep your `SCRIPT_DEBUG` on if developing with Dark Matter).
* Changed `npm run release` to use more optimised flags with Composer. (Only affects those developing Dark Matter and not the actual code in releases.)

= 2.1.0 =

* Added support and tests for Site Health Checks feature, originally introduced in WordPress 5.2. Tests the following;
  * Ensure the sunrise.php dropin is present.
  * Ensure the sunrise.php matches the version within Dark Matter.
  * Ensure the SUNRISE constant is setup correctly.
  * Checks FORCE_SSL_ADMIN is setup correctly and encourages best practices regarding HTTPS.
  * Checks COOKIE_DOMAIN to ensure it is not set.
  * Recommends a primary domain is set.
* Changed the behaviour when COOKIE_DOMAIN is set.
  * Now disables SSO (Single-Sign On) and no longer produces in a `wp_die()` error.
  * The new Site Health check will note an error if COOKIE_DOMAIN is set and state that SSO has been disabled.
* Added PHPCS through Composer for development.
  * Set to adhere to the WordPress-VIP-Go coding standards (https://wpvip.com/documentation/how-to-install-php-code-sniffer-for-wordpress-vip/).
  * Reorganised filenames to be all lowercase with hyphens and prefixed `class-` where appropriate.
  * Improved checks and sanitisation of Server Variables.
  * All spaces are tabs - courtesy of phpcbf - rejoice!
* Changed the way the SSO script is included.
  * The script tag is now created within JavaScript.
  * Unix epoch is appended to the URL for cache breaking.
  * Note; it is still recommended to exclude the `dark_matter_dmcheck` and `dark_matter_dmsso` is excluded from any request caching solution.
* When creating a new domain, the protocol field now defaults to HTTPS.
* Fixed the DM_URL->unmap() method doc block.
* Switch the CSS build from SASS to PostCSS and CSS / Webpack is updated accordingly.
* Removed an unused property in DM_UI class.
* Changed `wp darkmatter dropin` check to use the same test method from Site Health.
* Added localisation to the JavaScript UI components.
* Tested with WordPress 5.4.x.
* Contributing notes;
  * Added husky / lint-staged.
  * Pre-commit runs lint checks for JavaScript and PHP files.

= 2.0.5 =

* Updated Node dependencies where applicable.
* Added WebpackBar for a progress bar when building assets.
* Tested with WordPress 5.3.1.

= 2.0.4 =

* Fixed an issue where check for logging out users was causing a MIME Type error in some instances.
* A couple of improvements to Cookie SSO.

= 2.0.3 =

* Added and ensured that the no cache headers are used on all requests for the SSO flow.
  * This should aid with installations that utilise more pronounced caching setups.
* Modified the redirects to ensure that X-Redirect-By header is identified as "Dark Matter" rather than "WordPress".
* Added support for a new constant, DARKMATTER_SSO_TYPE, which can be set to a value of "disable" to stop SSO functionality.
  * In future, this will support a few SSO implementations depending on preference.

= 2.0.2 =

* Added logic to ensure that mapped domains are not considered "external" which was preventing oEmbeds from working.
* Fixed a warning notice for $is_admin.
* Updated npm dependencies.

= 2.0.1 =

* Fixed an issue causing `admin-post.php` requests to the Admin domain to be mistakenly redirected to Primary domain.
  * This is one of the request types which is be allowed on both the Admin and Primary domains.
* Fixed the version numbers to this release, 2.0.1, eliminating the beta flags.
* Improved Dark Matter behaviour for Sites which are not public, archived or deleted in a WordPress Network.
  * This only impacts sites using plugins which locked a site behind a login-gate; i.e. plugins such as More Privacy Options.
  * Improved the logic to prevent incorrect redirects.
  * Stopped Dark Matter mapping domains if the site is archived or deleted.
  * It is worth noting that you may still need to `darkmatter_allow_logins` depending on your setup.
* Ensured the 2.0.0 release notes in the readme.txt file to be accurate of all the changes.
* Added the Network flag to the plugin header, so that Dark Matter can only be activated at the Network-level.

= 2.0.0 =

* New Features;
  * Implements a new suite of WP CLI commands, see below. (https://github.com/cameronterry/dark-matter/issues/2)
  * Includes a feature for setting up Restricted domains, preventing use. (https://github.com/cameronterry/dark-matter/issues/13)
  * Field `is_active` is now included in the logic, allowed Sites to be prepped with a primary domain but activated at a later time. (https://github.com/cameronterry/dark-matter/issues/13)
  * REST API content now updates to use the primary domain (if set). Previously the domain used in the content would be the admin domain, regardless of whether the called endpoint was the admin domain or primary domain.
    * This also apples to XMLRPC endpoints.
  * Implemented the use of Object Cache API to significantly reduce database queries and improve performance.
    * For instance; even with a full page cache solution such as Batcache, at least two database queries - one to populate `$current_blog` (Site) and `$current_site` (Network) - on every request.
    * Once the cache is primed for the domains, the number of queries from Dark Matter should be nil 99.9% of the time.
  * Dark Matter is now available to be adjusted through custom REST API endpoints.
    * Covers both Domains and Restricted domains.
    * Domains has endpoints for retrieving all domains and domains for a specific website, as well as, adding, removing, and, updating domains.
    * Restricted domains has endpoints for retrieving Restricted domains, as well as, adding and removing Restricted domains.
    * All endpoints required authentication by a user who has Super Admin permissions.
  * Domain mapping logic now resides in its own folder.
  * All new admin user interface per Site to manage domains.
    * Built in React and uses the new REST API.
    * Can be disabled and hidden using the constant `define( 'DARKMATTER_HIDE_UI', true );`.
  * Allow Logins on Primary Domain option has been replaced with a filter, `darkmatter_allow_logins`.
    * Auto-detects bbPress plugin if it is installed and active.
    * Auto-detects WooCommerce plugin if it is installed and active.
  * Added a suite of actions and filters to enable extensibility of the Dark Matter plugin.
    * `darkmatter_domain_add` - fires after a domain is successfully added to the database and object cache.
    * `darkmatter_domain_basic_check` - fires at the end of the checks for a domain. Enables additional checks which are environment specific.
    * `darkmatter_domain_delete ` - fires after a domain is successfully deleted from the database and object cace.
    * `darkmatter_domain_updated ` - fires after a domain is successfully updated.
    * `darkmatter_primary_set ` - Fires after a domain is set as the primary domain for a site.
    * `darkmatter_primary_unset` - Fires after a domain is unset as the primary domain for a site.
    * `darkmatter_restrict_add` - Fires after a domain is successfully added to the Restricted list.
    * `darkmatter_restrict_get` - Fires between the Restricted domains being returned from Object Cache and then going to the database.
    * `darkmatter_restrict_delete` - Fires after a domain is successfully deleted to the Restricted list.
* Improvements over version 1.0.0;
  * Domains are now sanitized to ensure that is purely a domain and not a URL. (https://github.com/cameronterry/dark-matter/issues/29)
  * Priming the `$current_blog` now utilises `WP_Site`. (https://github.com/cameronterry/dark-matter/issues/17)
  * Better code structure in general.
  * Ensured that the mapping to the primary domain does not occur whilst the site is viewed through Customizer UI.
  * Streamlined the number of filters used to map the primary domain.
  * Mapping on the `the_content` filter now occurs later, to catch all URLs, including those from additional implementations such as `srcset`.
  * Better handling of differences between HTTP and HTTPS settings for admin and mapped domains.
    * For HTTPS mapped domains and HTTP admin domain, third party cookie authentication doesn't engage. But will for HTTPS admin domain and HTTP domains.
  * General Domain Mapping improvements.
    * Home URL (`get_home_url()` and `home_url()`) now map appropriately within the context of `switch_to_blog()` (https://github.com/cameronterry/dark-matter/issues/3).
    * "Visit" link on the Network Admin > Sites page now maps to the primary domain for each site.
    * "Visit Site" link on Admin Bar now maps to primary domain for the current site.
    * "My Sites" on Admin Bar now maps to primary domain for each website in the dropdown.
  * Redirects now occur at the `muplugins_loaded` action, much earlier in the process lowering the amount of WordPress which is loaded before sending a redirect header.
    * Logic for determining redirects to the primary domain has been streamlined.
  * Support for Gutenberg editor.
* Backward Compatibility
  * 2.0.0 will no longer find the first available domain if no primary domain is set.
    * Previously Dark Matter would utilise the next available secondary domain.
    * New version in the case of the Admin domain will not redirect and in the case of a domain found but not primary, will redirect to the Admin domain.
    * Some edge cases, Dark Matter may not engage and WordPress will redirect to the root website on the Network.
  * 1.x.x functions no longer exist. If you have use these, you will need to update your code accordingly.
  * 1.x.x version of **sunrise.php** will error on update as the require path has changed in the new folder structure of 2.0.0.

== Upgrade Notice ==

= 2.2.1 =

This contains a major fix for broken UI in 2.2.0, and should be updated as soon as possible. Apologies for inconvenience.

= 2.1.0 =

Dark Matter will note that sunrise.php does not match. This is due to an updated PHP comment and not a coding change.

= 2.0.0 = 

There is backward compatibility issues upgrading. Please ensure you read the release notes for 2.0.0 before upgrading!
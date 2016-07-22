=== Plugin Name ===
Contributors: cameronterry
Tags: domain mapping, multisite
Requires at least: 4.5
Tested up to: 4.6-beta3
Stable tag: 0.7.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WARNING: Dark Matter is a highly opinionated domain mapping plugin for WordPress multisites, designed
to work out of the box as-is with no setup.

== Description ==

Dark Matter is a highly opinionated domain mapping plugin for WordPress multisites, designed
to work out of the box as-is with no setup.

Unlike other plugins such as Donncha's "WordPress MU Domain Mapping" and WPMU Dev's premium
domain mapping plugin, Dark Matter offers virtually no options.

It is designed specifically to work as follows;

* To have separate domain for /wp-admin/ and for the front-end websites.
* Provide per blog administrator users the ability to map a domain for their website.
* Ensure that the WordPress Admin Bar is visible on all mapped domains using a basic single sign-on implementation.

== Installation ==

**Please note;** these installation steps are primarily for brand new multisites and not
for migrating from another domain mapping plugin.

= New / Standard Installation =

1. Add the plugin files to the /wp-content/plugins/ directory like any other plugin.
2. Go to the Network Admin > Plugins area and click "Network Activate".
3. Copy the /root/wp-content/plugins/dark-matter/sunrise.php to /root/wp-content/sunrise.php
4. Modify wp-config.php to include - define( 'SUNRISE', 'on' );

= Migrating from another domain mapping plugin =

T.B.D.

= Web Server steps =

On your web server software (e.g. Apache HTTPD or Nginx), ensuring that your configuration
permits multiple domains and / or the domains you specifically want to map to your WordPress
multisite.

Go to (URL) to get more information and examples of how to configure your Apache HTTPD or
Nginx setup.

== Frequently Asked Questions ==

= Why is Dark Matter so opinionated and has so few options? =

"With great power, comes great responsibility." Dark Matter is designed to deliver a single
multisite with a very specific configuration. The setups that it serves currently are
installations requiring the Admin area of WordPress to be on a separate domain from the main
websites and for users to be able to navigate cleanly between them.

Rationale? These specific installations require a redundancy which enables the Admin area
of WordPress to remain functional and usable in the event of a website failure. Having
separate DNS settings for each provides an easy (although depending on circumstances, maybe
not for all scenarios, the best) way to map front-end and admin-end to separate
infrastructure.

= Does my multisite need to be subdirectory or sub-domains? =

Dark Matter is designed and tested to work with both a sub-directory and a sub-domain
WordPress multisite.

= Does Dark Matter scale / work with large traffic websites? =

Dark Matter is currently in use on a single WordPress Multisite (sub-directory) handling
between 10 million and 15 million page views per month (according to Google Analytics) with
over 60 websites.

It is also in use for a much smaller WordPress Multisite (sub-domains) handling between
10,000 and 30,000 page views per month.

= Is Dark Matter tested with [insert name] plugin? =

Dark Matter has been tested to ensure it functions correctly with the following plugins;

* Advanced Custom Fields - https://wordpress.org/plugins/advanced-custom-fields/
* Custom Post Type Permalinks - https://wordpress.org/plugins/custom-post-type-permalinks/
* Jetpack by WordPress.com - https://wordpress.org/plugins/jetpack/
* Yoast SEO - https://wordpress.org/plugins/wordpress-seo/

= Is Dark Matter tested with [insert name] theme? =

* Hueman - https://wordpress.org/themes/hueman/
* Oxygen - https://wordpress.org/themes/oxygen/
* Twenty Eleven - https://wordpress.org/themes/twentyeleven/
* Twenty Fourteen - https://wordpress.org/themes/twentyfourteen/
* Twenty Sixteen - https://wordpress.org/themes/twentysixteen/
* Twenty Thirteen - https://wordpress.org/themes/twentythirteen/
* Twenty Twelve - https://wordpress.org/themes/twentytwelve/

== Changelog ==

= 0.8.0 (Beta) =

* Front-end redirect is now executed on parse_query action rather than template_redirect.
  * The way Yoast SEO sitemaps are generated means that template_redirect is never fired.
  * It has the added bonus of being a [handful of actions before template_redirect](https://codex.wordpress.org/Plugin_API/Action_Reference#Actions_Run_During_a_Typical_Request), so means less of WordPress loads before issuing the redirect and is bit more efficient.
* Fixed the logic inside the front-end redirect so that it no longer attempts to fix both a domain and protocol mismatch at the same time. Now it is one or the other.

= 0.7.0 (Beta) =

* Added primary domain to the allowed_redirect_hosts, so that it passes the validation for wp_safe_redirect().
* Set domain HTTPS API mechanism now respects the FORCE_SSL_ADMIN constant setting.
* Fixed the issue which caused the options on the Settings -> Domain Mapping admin panel to redirect to the Dashboard upon save.
* Fixed an issue in which the domain mapping would not fire or map correctly during a Cron job.
* Fixed an issue on the front-end domain mapping which now points admin URLs to the correct domain.

= 0.6.0 (Beta) =

* Removed URL mapping from the filters "stylesheet_directory" and "template_directory" as these filters handle folder paths. Not URLs.
* Fixed a regex bug with the URL mapping API not handling the difference between HTTP and HTTPS URLs.
* Changed the upgrade mechanism to have a separate version number for the plugin itself and the database.
  * This is to stop database upgrade procedure running when it is not needed.

= 0.5.0 (Beta) =

* Initial beta release.

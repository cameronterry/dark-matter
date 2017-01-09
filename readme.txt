=== Plugin Name ===
Contributors: cameronterry
Tags: domain mapping, multisite
Requires at least: 4.5
Tested up to: 4.7
Stable tag: 1.0.0 RC4
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WARNING: Dark Matter is a highly opinionated domain mapping plugin for WordPress
Networks, designed to work out of the box as-is with no setup.

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

= Migrating from another domain mapping plugin =

T.B.D.

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

= 1.0.0 =

* First release.
* [Fix] The first column of the Domain Mapping UI no longer just displays the digit one (1) but now the Domain ID.
* [Fix] Removed a breaking conditional when mapping "home" option on the admin side. This was causing issues with;
  * The "Visit Site" link in the Admin bar on the admin area.
  * Yoast SEO snippet preview when Editing a post / page.

= 1.0.0 Release Candidate 4 =

* Cleaned up and eliminated some code duplication on handling redirects from the admin actions for Add / Remove HTTPS, Make Primary, etc.
* [New] Dark Matter will now warn Super Admins if the SUNRISE constant is not detected.
* [Fix] admin_url() will no longer map to the primary domain if the URL contains "admin-ajax.php", as usage of this is common for AJAX powered plugins (for now).
  * This only occurs if WordPress is viewed through a "mapped domain" (primary or not) and should leave the admin-side untouched.
* [Fix] Redirects no longer erroneously contain a trailing forward slash after all the query string parameters.
* [Fix] An issue where sub-folder Networks would not redirect properly if omitting the trailing forward slash.
  * For example; wordpress.test/sitetwo would redirect to www.example.com/sitetwo instead of www.example.com
* [Fix] If WP REST API v1.x.x is in use, then Dark Matter will no longer redirect to the primary domain.

= 1.0.0 Release Candidate 1 =

* pre_option_home now checks to make sure the URL is actually the one used for the domain mapping before changing it.
* This should improve the issue where Network Admin "Visit Sites" was being mapped for all websites and not just the one the user is looking at.
* Tidied up the readme.txt file to;
  * Standardise on the usage of WordPress Network / Network rather than WordPress Multisite / Multiste.
  * Line breaks now inline with Atom text editor.
* [Bug] $wpdb is now explicitly global rather than implied in sunrise.php file.
* [Bug] Fixed a bug with sub-folder WordPress Networks where redirecting from the Admin Domain to the Primary Domain would in some scenarios omit the forward slash between Domain and Request URI.
* [Enhancement] Removed an unused parameter in dark_matter_api_get_domain().
* [Enhancement] Removed some unnecessary wp_die() calls on admin actions as the logic meant they would never be reached.
* [Enhancement] Removed an unused variable on dark_matter_blog_admin_menu() which was designed to catch the hook name. But the implementation changed direction.
* [Enhancement] Removed unnecessary declaration of $path variable inside dark_matter_redirect_url() function.

= 0.11.0 (Beta) =

* Completely rewritten the redirects logic to fix the following issues;
  * Post previews not working and being served from the mapped domain (rather than the admin domain).
    * This is because the parse_query action runs too earlier and the is_preview() API returns "false" rather than "true".
  * To ensure the Admin area redirects properly.
  * To ensure the Login page is always served on the admin domain.
* Fixed a bug where redirects from the admin domain to the mapped domain included an extra forward slash in the domain.
  * This in turn caused a double-redirect; one from the admin domain to the mapped domain and then a second from the double-slashed version to the single-slashed version.

= 0.10.0 (Beta) =

* Fixed a typo with dark_matter_map_content() which prevented the logic handling array types (like upload_dir).
* Put in additional logic in dark_matter_map_content() so that it doesn't accidentally convert booleans to strings.
* The front-end redirect logic now detects if wp-login.php or wp-register.php is in use and exits, to let a more suitable process handle the redirection logic.
* Moved the dark_matter_prepare action to execute immediately upon inclusion of the dark-matter.php file and BEFORE the rest of the plugin loads.
  * This is so that actions and filters can be executed from being added in the scenario of a website not having a mapped domain.
* API dark_matter_api_get_domain_primary() now returns null rather than the original domain.
* Now checks to make sure we have a primary domain before attempting to change URLs.
* XMLRPC requests are no longer redirected.
  * This was preventing sites from connecting to WordPress.com for Jetpack functionality.
  * Jetpack now connects but the debugger fails with a "Could not validate security token" if Jetpack is not connected. However, if once connected the debugger claims all is fine!
* A lot of the fixes solves problems with sites which have no mapped domains.

= 0.9.0 (Beta) =

* Removed the Network Admin page as it is currently has no options or need.
* Added the L10n API calls where relevant in the code for localisation.
* Standardised indentation to tabs rather than spaces for all files.
* Added a license block, as recommended in the WordPress.org Plugin Handbook.
* Changed the sunrise.php so that grunt work version is in the dark-matter/inc/ folder and the one to be used inside wp-content/ references the one inside the plugin directory.
  * This should make upgrades more seamless without manual intervention from site owners in the future.
* Put in an activation hook which can copy the sunrise.php to the correct destination depending on file and folder read / write permissions.
* Removed a superfluous check on version number when retrieving the primary domain.
* Removed the sarcasm from the error message when someone has defined a "COOKIE_DOMAIN" value.

= 0.8.0 (Beta) =

* Front-end redirect is now executed on parse_query action rather than template_redirect.
  * The way Yoast SEO sitemaps are generated means that template_redirect is never fired.
  * It has the added bonus of being a [handful of actions before template_redirect](https://codex.wordpress.org/Plugin_API/Action_Reference#Actions_Run_During_a_Typical_Request), so means less of WordPress loads before issuing the redirect and is bit more efficient.
* Fixed the logic inside the front-end redirect so that it no longer attempts to fix both a domain and protocol mismatch at the same time. Now it is one or the other.
* Completed testing with Yoast SEO and Custom Post Type Permalinks plugins.

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

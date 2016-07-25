# WARNING

**This plugin is still under development and testing. So if you do decide to use it, then be prepared for bugs and other oddities.**

# Dark Matter

Dark Matter is a highly opinionated domain mapping plugin for WordPress multisites, designed to work out of the box as-is with no setup.

Unlike other plugins such as Donncha's "WordPress MU Domain Mapping" and WPMU Dev's premium domain mapping plugin, Dark Matter offers virtually no options.

It is designed specifically to work as follows;

* Works only with WordPress Multisite installations and not for single site installation (sorry!)
* To have separate domain for /wp-admin/ (admin domain) and for the front-end websites (blog domain).
* Previews are viewed on the Admin domain.
  * This is to ensure Preview functionality continues to work in the event of a single-sign on failure.
* Provide per blog administrator users the ability to map a domain for their website.
* Ensure that the WordPress Admin Bar is visible on all mapped domains using a basic single sign-on implementation.
  * This is currently implemented using third-party cookies.

# WARNING

**Whilst the terms and conditions of this plugin do not ask you to surrender
your soul to us... once it has finished breaking your site, it might as well
feel that it has!**

# Dark Matter

Dark Matter is a highly opinionated domain mapping plugin for WordPress
Networks, designed to work out of the box as-is with no setup. Unlike other
plugins such as Donncha's "WordPress MU Domain Mapping" and WPMU Dev's premium
domain mapping plugin, Dark Matter offers virtually no options.

## What Dark Matter does and does not

The following is the list of implementation decisions taken and specifically
built for during Dark Matter's development.

The following is considered the **tenets** which will govern decisions on what
feature requests / bugs are considered and what feature requests / bugs are
rejected.

* **Does**;
  * The admin area is on a separate domain (Admin domain) from the front-end (Primary Domain - also Auxillary Domain).
  * A basic single-sign on solution to show the Admin bar on "primary domains".
  * Customizer and Preview work on the "Admin domain" with no redirection.
  * Provide per blog administrator users the ability to map a domain for their website.
  * WP REST API works on both "Admin domain" and "primary domain", so that it can be used in the Admin area and front-end.
* **Does not**;
  * Provide any domain mapping options for the root website.
  * For the admin area to work on the "primary domain" (it will use the "Admin domain" always.)
  * Permit a single website to work on multiple domains.
  * Enable protocol on a per Post / Page basis.
  * Tested to work with a single installation running multiple WordPress Networks.

## Version Support

The following is the list of current version support that Dark Matter is
currently catering for. If the version you are using is not listed below, then
Dark Matter may work but the developers have yet to test it.

* Apache HTTPD;
  * 2.4.x
* IIS (Internet Information Services);
  * Not tested to date.
* Nginx;
  * 1.4.x (Ubuntu 14.04)
  * 1.10.x (Ubuntu 16.04)
* PHP;
  * 5.5.x
  * 5.6.x
  * 7.0.x
  * 7.1.x
* WordPress;
  * 4.8.x
  * 4.9.x

## Reporting problems

You can use the Issues system here on Github to report problems with the Dark
Matter plugin. To aid and speed-up diagnosing the problem, you are best to
include as much as the following as you possibly can;

* Check here to ensure the problem has not been reported by someone else.
* WordPress;
  * Version of WordPress itself.
  * List of active plugins (installed but unused _should_ rarely cause problems)
* Hosting information;
  * Either
    * Apache / Nginx
    * Operating System (Linux or Windows)
  * Or;
    * Host provider (Digital Ocean, Dreamhosts, GoDaddy, WP Engine, etc)
* Browser (Chrome, IE, Firefox, Opera, etc with version)
* Any additional information such as;
  * Using Cloudflare.

All issues reported are taken seriously and are checked, but please bear in mind
that responses are not always instant.

## Contributing

Please ensure you have read**What Dark Matter does and does not** first and make
sure to read the notes below. But don't be dissuaded, pull requests are welcome
:-)

### Syntax

Dark Matter does not have a coding style guide but there are several rules which
should be observed;

* Unix line breaks.
* Tabs, not spaces.
* [Yoda conditions](https://en.wikipedia.org/wiki/Yoda_conditions).
* Code should be concise rather than terse.
* Comments should not extend beyond the 80th character (default in Atom) unless;
  * Text for a bullet point.
  * Code example in comments.
  * PHPDoc conventions for @@link or @@param in describing a class or function.

### URLs to check

The following is a list of example URLs which are worth checking (depending on
the change) when developing with Dark Matter.

Using www.wpnetwork.com as the "Admin domain" and www.example.com as the
"Primary domain", Dark Matter should be tested with the following URLs;

* http://www.wpnetwork.com/sitetwo/ (with trailing forward slash) => http://www.example.com/
* http://www.wpnetwork.com/sitetwo (without trailing forward slash) => http://www.example.com/
* http://www.wpnetwork.com/sitetwo/index.php (query string processing, without trailing forward slash) => http://www.example.com/
* http://www.wpnetwork.com/sitetwo/index.php/ (query string processing, with trailing forward slash) => http://www.example.com/
* http://www.wpnetwork.com/sitetwo/?utm_source=test (with query string) => http://www.example.com/?utm_source=test
* http://www.wpnetwork.com/sitetwo?utm_source=test (with query string, without trailing forward slash) => http://www.example.com/?utm_source=test
* http://www.wpnetwork.com/sitetwo/#test (hash URL test) => http://www.example.com/
* http://www.wpnetwork.com/sitetwo#test (hash URL test, without trailing forward slash) => http://www.example.com/

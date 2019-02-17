# WARNING

**Whilst the terms and conditions of this plugin do not ask you to surrender
your soul to us... once it has finished breaking your site, it might as well
feel that it has!**

# Dark Matter

Dark Matter is a highly opinionated domain mapping plugin for WordPress
Networks, designed to work out of the box as-is with no setup. Unlike other
plugins such as Donncha's "WordPress MU Domain Mapping" and WPMU Dev's premium
domain mapping plugin, Dark Matter offers virtually no options.

## CLI Commands

### Add / Update / Remove Domains

Examples of adding, removing and updating a domain for a Site.

```
wp --url="sites.my.com/siteone" darkmatter domain add www.example.com --primary --https
wp --url="sites.my.com/siteone" darkmatter domain remove www.example.com
wp --url="sites.my.com/siteone" darkmatter domain remove www.example.com --force
wp --url="sites.my.com/siteone" darkmatter domain set www.example.com --primary
wp --url="sites.my.com/siteone" darkmatter domain set www.example.com --secondary
```

### Listing Domains

Examples of listing domains for a Site.

```
wp --url="sites.my.com/siteone" darkmatter domain list
wp --url="sites.my.com/siteone" darkmatter domain list --format=json
```

Examples of listing domains for the entire Network.

```
wp darkmatter domain list
wp darkmatter domain list --format=csv
```

Retrieve all the primary domains for the Network.

```
wp darkmatter domain list --primary
```

### Reserving Domains

Reserving a domain. This allows an administrator to setup the primary and / or secondary domains but stop Dark Matter performing redirects and rewrites. Please note; domains are enabled by default.

```
wp --url="sites.my.com/siteone" darkmatter domain add www.example.com --primary --https --disable
wp --url="sites.my.com/siteone" darkmatter domain set www.example.com --enable
wp --url="sites.my.com/siteone" darkmatter domain set www.example.com --disable
```

### Restricting Domains

Examples of adding and removing a restricted domains for a Network. This permits administrators to stop domains from being used for a WordPress website; useful for organisations which use multiple CMS.

```
wp darkmatter restrict add www.example.com
wp darkmatter restrict remove www.example.com
```

Examples of retrieving a list of all restricted domains for a Network.

```
wp darkmatter restrict list
wp darkmatter restrict list --format=json
wp darkmatter restrict list --format=ids
```

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

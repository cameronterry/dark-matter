# Contributing

Thank you for your interest in contributing to Dark Matter. This document is intended more for software developers who
wish to contribute code to the plugin. Whilst long, this document is certainly not exhaustive of the inner workings of
the plugin, but are meant as helpful information in your path to submitting your own contribution.

As it is part of the code base, you may as contribute additional information to this document as well as code!

## Things to Keep in Mind

Dark Matter is named after a proposed phenomenon to explain approximately 85% of the matter in the universe and is
considered to be an attracting force within cosmos. The key concept from the cosmological "dark matter" is: whilst we
cannot see it, we know it is there through observation and its function to the known universe. Dark Matter, the
WordPress plugin, is designed in a similar fashion. An almost completely invisible additional functionality to WordPress
but by observation - visiting a site with a mapped domain - and its function - providing the option for mapping domains
- are aware it is there. Also, the acronym - D.M. - matches that for "domain mapping".

### Notes to Self

* If your change completely breaks your local WordPress setup, don't worry. It happens, and on this plugin, often! Parts
  of Dark Matter operate at a very low-level within the operation of WordPress, therefore even a very small change can
  break a lot. Every developer who has contributed or debugged a line of code has been there, so you are not alone.
* Dark Matter is often paired with WordPress installs which are running hundreds of websites. Some of these installs are
  big in both traffic and size. Don't be daunted by this. We also support very small websites too.

### Decisions on Architecture and Functionality

* Dark Matter is highly opinionated on how it works and what functionality is provided. That is why it comes in one
  configuration: an admin domain for the admin side and mapped domains for the public-facing side. This is to achieve
  two specific goals: 1) reliably and 2) simplicity (for administrators). This also happens to follow the philosophy of
  WordPress Core, which favours [decisions, not options](https://wordpress.org/about/philosophy/#decisions).
  * The other type of domain mapping, which will map both the admin-side and public-facing side, is available through
    WordPress Core itself. Kinsta has an [excellent and detailed article on explaining how to use it](https://kinsta.com/knowledgebase/wordpress-multisite-domain-mapping/#step-3--update-wpconfigphp-file)
    and these steps are for any WordPress install, not only for their platform.
  * Since WordPress can do this itself, is why Dark Matter does not offer the option.
* As noted in the preface paragraph for this section, Dark Matter is a largely invisible plugin.
* Some of the functionality is deliberately hidden in the WP-CLI integration as the functionality is restricted to
  systems administrators in some circumstances.
* As the core contributors here either work or are supporting large organisations, you may find that code for specific
  area may change suddenly and without warning. Likewise when submitting Issues through Github. Please don't be
  disheartened. This occurs as large organisations prefer privacy regarding their code bases, however Dark Matter some
  times requires detailed specifics to fix bugs (we do not accept new feature requests in private). As such, these
  conversations and bug fixes may supersede contributions but where possible, we will endeavour to evaluate as and when
  this occurs. Who knows, your fix might be more elegant and a better fit than what we purpose! 

### Developing  

* Simplicity is a key principle for the code and how to use Dark Matter.
* Often and small commits are recommended.
* Test many websites with different domain mappings.
* Parts of Dark Matter interact with code inside WordPress that is rarely mentioned and hard to find information on.
  Later you will notice that XDebug is required and this is one of the main reasons. You will also notice there are lots
  of comments in the code, as some obscure behaviours are very hard to remember.
* For now (...) we have opted against using a PSR-4 autoloader from Composer. This will likely be considered in version
  3.0.0 of Dark Matter when that development begins.
* On the topic of Composer, we are also currently not accepting suggestions for any third party libraries which will be
  included as part of the code we distribute. 

## Local Environment for Development

### Prerequisites

The Dark Matter repository only contains code exclusive to the plugin and does not include WordPress or other plugins.
Therefore to get up and running, you will need the following prior to cloning Dark Matter.

#### Required

* A text editor with support for Editorconfig and XDebug: PhpStorm, VSCode, etc.
* Composer.
* Memcache (or Redis).
* Node LTS and NPM. ([NVM](https://github.com/nvm-sh/nvm) is commonly used.)
* PHP 7.
* Self-signed certificate process for HTTPS.
* Web server software, most common being Apache HTTPD and NGINX.
* WordPress, setup as a Multisite (sub-folder or sub-domain).
* WP CLI.
* XDebug.

Whilst this can be setup manually, developers for Dark Matter often use a specialised toolset which simplifies the setup
and install of these prerequisites. Most commonly used are:

* [Pilothouse](https://github.com/Pilothouse-App/Pilothouse)
* [WP Local Docker](https://10up.github.io/wp-local-docker-docs/)
* [VVV](https://varyingvagrantvagrants.org/)

Once your environment is setup and running, you will need to add additional domain names in order to test the domain
mapping. We recommend using the following setup:

* darkmatter.test (admin domain)
* domainone.test (mapped domain)
* domaintwo.test (mapped domain)
* domainthree.test (mapped domain)
* domainfour.test (mapped domain)

#### Recommended

The following includes a number of plugins which are recommended to install and use, where appropriate, when developing
with Dark Matter. The plugins listed for debugging are useful for finding important information whilst testing new code
or finding circumstances used. Meanwhile the plugins for compatibility cover the most functionality included with most
installs of WordPress that utilise Dark Matter. Any contributions will be tested with these plugins.

* The following plugins for debugging:
  * [Query Monitor](https://wordpress.org/plugins/query-monitor/)
  * [Rewrite Rules Inspector](https://wordpress.org/plugins/rewrite-rules-inspector/)
  * [Yoast Test Helper](https://wordpress.org/plugins/yoast-test-helper/)
* The following plugins for compatibility:
  * [Classic Editor](https://wordpress.org/plugins/classic-editor/)
  * [Gutenberg (plugin for future changes coming to WordPress Core)](https://wordpress.org/plugins/gutenberg/)
  * [Yoast SEO](https://wordpress.org/plugins/wordpress-seo/)

#### Optional

Whilst optional, most of the plugins listed here are used on websites which are powered by Dark Matter. These plugins
tend to have wide adoption - such as Advanced Custom Fields and Contact Form 7 - or a specialised towards large
WordPress websites - such as Batcache and ElasticPress.

These tend to be tested on a more ad-hoc basis depending on requests and needs of people who are using Dark Matter. In
general, unless you are specifically using these plugins, you can skip adding these to your local install of WordPress.

* [Advanced Custom Fields](https://wordpress.org/plugins/advanced-custom-fields/)
* [Advanced Custom Fields PRO](https://www.advancedcustomfields.com/pro/)
* [Akismet](https://wordpress.org/plugins/akismet/)
* [AMP](https://wordpress.org/plugins/amp/)
* [Batcache](https://github.com/Automattic/batcache)
* [Contact Form 7](https://wordpress.org/plugins/contact-form-7/)
* [ElasticPress](https://wordpress.org/plugins/elasticpress/)
* [WooCommerce](https://wordpress.org/plugins/woocommerce/)

### Setup Dark Matter

#### Getting the Code From Github

Please follow the steps for forking a repository on Github's [documentation here](https://docs.github.com/en/free-pro-team@latest/github/getting-started-with-github/fork-a-repo).

Please note: the following utilises the CLI client for git.

Navigate to the plugins folder:
```
cd [wordpress]/wp-content/plugins/
```

And then clone the Dark Matter repository. Please note: the plugin will not be ready for use until the next few steps.

```
git clone git@github.com:[your-username-here]/dark-matter.git
```

This will bring the Dark Matter files onto your computer from the `develop` branch. This is the main branch used for
core development of the plugin and is safe for submitting pull requests to. At this point, you will likely want to
create a new branch if you are wishing to contribute a change to Dark Matter. The most common convention used is
`bug/[shortened-name]` and `feature/[shortened-name]`.

Move your terminal location into the folder for Dark Matter.

```
cd dark-matter/
```

#### A Note About What is Included

Prior to running the commands, you should be aware of some of the workings included. Specifically the use of
[Husky](https://typicode.github.io/husky/#/) as this can cause some confusion if you are familiar with git. After the
"start" script has run, Husky is used to install pre-commit hooks for this repository which in turn will run code
quality and standards checks every time you commit code to the repository. This ensures that any CSS, JavaScript, and /
or PHP, adheres to the standards that are used for this plugin and is a first step guarantee to ensuring your code is
ready.

This project also uses [PHPCS](https://github.com/squizlabs/PHP_CodeSniffer) for the code quality checks on PHP files.
However this is handled through the Composer dependencies and is isolated to this repository rather than set globally.
This is to ensure that Dark Matter is not impacted by any global settings nor itself impacts any of your global
settings, as most developers who contribute code to Dark Matter work across multiple projects with differing
requirements and standards adherence.

We follow the coding standards for WordPress.com VIP Go, although please note that we are not aware of Dark Matter being
used on any install on VIP Go. This standard has a number of advantages over the WordPress Coding Standards and has been
chosen specifically for the additional focus around performance and security. You can find more information on the VIP
Coding Standards at the following URLs:

* [Github Repository](https://github.com/Automattic/VIP-Coding-Standards)
* [How to install PHP_CodeSniffer for WordPress VIP](https://wpvip.com/documentation/how-to-install-php-code-sniffer-for-wordpress-vip/)

#### Running the Build Commands

Dark Matter utilises both composer - for code quality and standards - as well as npm - for building CSS and JavaScript
files - as part of the build process. To get up and running, we run the start command through NPM.

```
npm run start
```

The "start" script is essentially an alias which runs the following commands all at once.

```
composer install
npm install
npm run build
npm run build-dev
```

Once complete, and assuming there are no errors, then Dark Matter is now ready for use and development! Going to your
local WordPress install, you will now be able to network activate the plugin and configure it for use.
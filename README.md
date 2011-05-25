# Elefant

Elefant is the MVC framework in PHP re-imagined. Elefant is based on
12 years of programming in PHP and running a monolithic CMS project
[www.sitellite.org](http://www.sitellite.org/) that despite its huge size
did get a few things right that I haven't seen elsewhere. This is my attempt
at taking those things and starting fresh.

This framework doesn't look much like other PHP-based MVC frameworks, but
I've never been able to stomach the disconnect they have with the language
itself. PHP isn't the most elegant language, and MVC constructs used in other
languages feel clunky when translated into it. But if we flip it around and
play on PHP's strengths, we get something much more natural to code with in
our little monster of a "hypertext preprocessor" ;)

## What's Missing

This is a simple framework, but it is very capable and provides everything
you need in a core MVC framework. There are lots of things I tried to leave
out to keep things simple, and some things that I'm sure will appear down
the road. If something you need is missing and you feel it might fit as
part of the core framework, feel free to contribute!

Elefant apps are self-contained, so you can also make reusable apps and share
them with others as well.

Some obvious missing pieces:

* Permissions/access control
* Caching (templates are compiled so they'll be opcode-cached) - memcache
* RSS/XML/etc parsing - SimplePie
* Search engine
* Email management
* Deployment management
* Logging engine

For a more complete discussion on missing pieces and the future of Elefant,
see the [Future page on the wiki](https://github.com/jbroadway/elefant/wiki/Future).

## What *is* here?

* Really simple URL routing w/ friendly URLs
* Secure database abstraction/modeling (based on PDO)
* Compiled templates with output filtering *on* by default
* Flexible input validation (server-side and in-browser)
* Simple form handling
* Customizable user authentication
* Internationalization/localization
* As little scaffolding as possible
* An example /admin app for editing pages
* [High quality documentation](https://github.com/jbroadway/elefant/wiki)
* Near 100% unit test coverage
* [Speed](https://github.com/jbroadway/elefant/wiki/Performance). Less cruft, faster pages.

Request routing is where Elefant really stands out. A handler is simply a
PHP script and mapping is automatic. You can write your handlers just like
you would any other PHP script, starting at the top and using echo when you
want to output something. At the end, they're handled properly and inserted
into the right template for you. Just like that.

## Why?

Because after all these years, and after writing a lot of code in a lot of
different languages, I still don't mind PHP. It's a good tool for getting
certain jobs done quickly, and for me this helps make it a little easier/less
painful. Hopefully it helps others do the same.

I also wanted a clean start, not being tied to supporting older versions of
PHP and a ton of legacy code that users depended on. This means I can choose
more elegant and efficient ways of solving things, and learn from past
mistakes/luck and do it even better this time.

## Getting Started

1. Download the latest from GitHub:

http://github.com/jbroadway/elefant

2. Unzip into a site root somewhere (no sub-folders, use sub-domains instead).
Change the permissions on folders conf and cache to `0777`.

3. Edit `conf/config.php` and add your database connection info.

4. Run the appropriate `conf/install_*.sql` file to create the tables for the
admin example handlers.

5. Go to your site and see that it worked. You should see a basic welcome page
if all went well.

6. Go to `/admin` and you can log in with the master username and password
from your global configuration. This is a really really basic admin area for
editing web pages. It exists to give you some example code to read and help
you get started, and if you want to improve on it and contribute that back to
the project, I will love you forever :)

7. Edit `layouts/default.html` and add your site stylings.

8. Create an app and write some models/handlers/views. Lather, rinse, repeat.

9. The GitHub page is the place to go for issues and info. If there's a need,
I'll make a Posterous Group for it as well, so let me know if you think that
would be good to have.

## Folder Layout

* .htaccess - rewrites and permissions for Apache
* apps - your apps go here
* cache - templates rendered to PHP
* conf - global configurations
* css - global CSS files
* index.php - the front-end controller, or request router
* js - global Javascript files
* layouts - design layouts
* lib - main libraries
* nginx.conf - rewrites and permissions for Nginx
* README.md - this file
* tests - unit tests

## Example Code

### 1. A basic handler: hello.php

	<?php echo 'Hello ' . $_REQUEST['name']; ?>

Save this to `apps/hello/handlers/index.php` and you can access it via `/hello` in your
browser.

### 2. Using URL components in handlers:

	<?php echo 'Hello ' . $this->params[0]; ?>

Now try calling that one via `/hello/world`. Extra values that didn't match the
handler is part of the `$controller->params` array for you.

### 3. Specifying an alternate template:

	<?php
	
	$page->template = 'alternate';
	
	echo 'Hello world';
	
	?>

I should mention, `$this` in a handler refers to the controller, although not
necessarily the global one (since handlers can call each other as well).

### 4. Defining extra variables for your template:

	<?php
	
	$page->title = 'My Page';
	$page->sidebar = 'Some sidebar content.';
	
	echo 'Regular output is the body content.';
	
	?>

Now in your template you can use `{{ title }}` and `{{ sidebar }}` just like
`{{ body }}` outputs the regular output.

### 5. From one handler to another:

	<?php
	
	$page->sidebar = $this->run ('myapp/sidebar');
	
	?>

Or from inside a template:

	<?php echo $controller->run ('myapp/sidebar'); ?>

## Code Conventions

I've chosen the following naming conventions for the core libraries:

* Classes start with capitals and use camel case.
* Methods and functions use underscores (like Ruby :)

I also use tabs instead of spaces, trailing braces instead of giving them
their own lines, and put a space before open braces and between operators,
for example:

	function foo_bar ($foo = false) {
		if (! $foo) {
			// etc.
		}
	}

Other than that, for documentation I use JavaDoc-style commenting and for
inline comments I use the double-slash.

## FAQ

Q. Do you know you spelt Elephant wrong?

A. This was my attempt at being hip and cool. No good?

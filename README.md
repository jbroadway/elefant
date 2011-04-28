# Pink Elefant

This is the PHP framework behind the PinkTicket.ca website.
It was created as my take on how PHP MVC should be done,
based on 10 years of programming in PHP and running a
long-time monolithic CMS project (www.sitellite.org) that
despite its hugeness still got some things right. This is
my attempt at taking those and starting fresh.

This framework doesn't look much at all like most other
PHP-based MVC frameworks, but I've never been able to
stomach the disconnect they had with the language itself.
PHP isn't a very elegant language in a lot of ways, and
the MVC constructs used in other languages feel very
unnatural and clunky when translated into PHP. But if we
flip it around and play on PHP's strengths, I think we
can come up with something much more PHP-esque and
more natural to code in in our little monster of a
"hypertext preprocessor" :)

## What's Missing

In short: A lot. Why? Because you can get it elsewhere
and include it yourself. This is a starting point, not the
kitchen sink. That was my problem last time, and I'm not
going down that road again.

For example, I have another little library I wrote
called PHPActiveResource that I often use to communicate
with Rails-based APIs. While I could include that here,
it's not essential so I left it out. If I need it, I
simply drop it into my lib folder, require it and I'm
on my way.

What else is missing?

* Session/user/auth handling - Zend_Session, Zend_Auth
* Permissions/access control - Zend_Acl
* Caching (templates do compile to PHP) - Memcache, Zend_Cache
* Logging - error_log, Zend_Log
* Forms/validation - Zend_Form, Zend_Validate
* RSS/XML/etc parsing - SimplePie, Magpie
* Unit testing - PHPUnit, SimpleTest
* AJAX handling - jQuery + json_encode/json_decode
* Internationalization/localization - Zend_Locale, Zend_Translate
* Documentation - Pocco, Phrocco
* Search - Zend_Search_Lucene, IndexTank
* Mail - Zend_Mail, SendGrid
* Deployment - loads of options/configurations abound

## What *is* here?

* Really simple URL routing
* Secure database abstraction/modeling
* Compiled templates with output filtering *on* by default
* As little scaffolding as possible

On this last point is where Pink Elefant really stands out
from the crowd (for better or worse). A handler is simply a
PHP script and mapping is automatic. You can write your
handlers just like you would any other PHP script, starting
at the top and using echo when you want to output something.
At the end, they're handled properly and inserted into the
right template for you. Just like that.

## Why?

Because after all these years, and after writing a lot of code
in a lot of different languages, I still don't mind PHP. It's
a good tool for getting certain jobs done quickly, and for me
this helps make it a little easier/less painful. Hopefully it
helps others do the same.

## Examples

### 1. A basic handler: hello.php

<?php echo 'Hello ' . $_REQUEST['name']; ?>

Save this to handlers/hello.php and you can access it via
/hello in your browser.

### 2. Using URL components in handlers:

<?php echo 'Hello ' . $this->params[0]; ?>

Now try calling that one via /hello/world. Extra values that
didn't match the handler is part of the $controller->params
array for you.

### 3. Specifying an alternate template:

<?php

$page->template = 'alternate';

echo 'Hello world';

?>

I should mention, $this in a handler refers to the controller,
although not necessarily the global one (since handlers can call
each other as well).

### 4. Defining extra variables for your template:

<?php

$page->title = 'My Page';
$page->sidebar = 'Some sidebar content.';

echo 'Regular output is the body content.';

?>

Now in your template you can use {{ title }} and {{ sidebar }}
just like {{ body }} outputs the regular output.

### 5. From one handler to another:

<?php

$page->sidebar = $this->run ('/sidebar');

?>

Or from inside a template:

<?= $controller->run ('/sidebar'); ?>

## Code Conventions

I've chosen the following naming conventions for the core libraries:

- Classes start with capitals and use camel case.
- Methods and functions use underscores (like Ruby :)

I also use tabs instead of spaces, trailing braces instead of giving
them their own lines, and put a space before open braces and between
operators, for example:

function foo_bar ($foo = false) {
	if (! $foo) {
		// etc.
	}
}

Other than that, for documentation I use JavaDoc-style commenting
and for inline comments I use the double-slash.

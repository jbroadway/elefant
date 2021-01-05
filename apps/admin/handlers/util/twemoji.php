<?php

/**
 * Adds twemoji character support to a web page.
 *
 * Usage:
 *
 * ### 1. Load this handler either in your handler
 *
 *     $this->run ('admin/util/twemoji');
 *
 * Or:
 *
 *      $this->run ('admin/util/twemoji?body=true');
 *
 * Or anywhere in your view:
 *
 *      {! admin/util/twemoji !}
 *
 * Or:
 *
 *      {! admin/util/twemoji?body=true !}
 *
 * ### 2. Use the emojiOne library:
 *
 *     twemoji.parse (some_string);
 *
 * For more usage info, visit:
 *
 * https://github.com/twitter/twemoji
 */

$page->add_script ('https://twemoji.maxcdn.com/v/13.0.1/twemoji.min.js', 'head', '', 'sha384-5f4X0lBluNY/Ib4VhGx0Pf6iDCF99VGXJIyYy7dDLY5QlEd7Ap0hICSSZA1XYbc4', 'anonymous');
$page->add_style ('<style>img.emoji { height: 1em; width: 1em; margin: 0 0.5em 0 0.1em; vertical-align: -0.1em; } </style>');

if (isset ($data['body'])) {
	$page->add_script ('<script>document.addEventListener (\'DOMContentLoaded\', function () { twemoji.parse (document.body); });</script>', 'tail');
}

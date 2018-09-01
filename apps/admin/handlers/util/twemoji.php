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

$page->add_script ('//twemoji.maxcdn.com/2/twemoji.min.js?11.0');
$page->add_style ('<style>img.emoji { height: 1em; width: 1em; margin: 0 0.5em 0 0.1em; vertical-align: -0.1em; } </style>');

if (isset ($data['body'])) {
	$page->add_script ('<script>$(function () { twemoji.parse (document.body); }); </script>');
}

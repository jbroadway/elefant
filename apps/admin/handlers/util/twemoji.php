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

$page->add_script ('https://twemoji.maxcdn.com/v/12.1.5/twemoji.min.js', 'head', '', 'sha384-E4PZh8MWwKQ2W7ANni7xwx6TTuPWtd3F8mDRnaMvJssp5j+gxvP2fTsk1GnFg2gG', 'anonymous');
$page->add_style ('<style>img.emoji { height: 1em; width: 1em; margin: 0 0.5em 0 0.1em; vertical-align: -0.1em; } </style>');

if (isset ($data['body'])) {
	$page->add_script ('<script>$(function () { twemoji.parse (document.body); }); </script>');
}

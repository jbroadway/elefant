<?php

/**
 * Displays a breadcrumb menu using a bulleted list that you can
 * apply CSS to with the `breadcrumb` class, for example:
 *
 *     .breadcrumb {
 *         list-style-type: none;
 *         margin: 0;
 *         padding: 0;
 *     }
 *
 *     .breadcrumb li {
 *         list-style-type: none;
 *         margin: 0;
 *         padding: 0;
 *         display: inline;
 *     }
 *
 *     .breadcrumb li:before {
 *         content: " / ";
 *     }
 *
 *     .breadcrumb li:first-child:before {
 *         content: "";
 *     }
 */

$n = Link::nav ();
$path = $n->path ($page->id, true);
$home_id = conf ('I18n', 'multilingual') ? $i18n->language : 'index';
$home = array ($home_id => __ ('Home'));
$path = ($path) ? $path : $home;
if (! in_array ($home_id, array_keys ($path))) {
	$path = array_merge ($home, $path);
}

echo "<ul class=\"breadcrumb\">\n";
foreach ($path as $id => $title) {
	if ($id != $page->id) {
		printf ("<li>%s <span class=\"divider\">/</span></li>\n", Link::make ($id, $title));
	} else {
		printf ("<li class=\"current\">%s</li>\n", $title);
	}
}
echo '</ul>';

?>
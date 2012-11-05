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

$n = new Navigation;
$path = $n->path ($page->id, true);
$home = array ('index' => i18n_get ('Home'));
$path = ($path) ? $path : $home;
if (! in_array ('index', array_keys ($path))) {
	$path = array_merge ($home, $path);
}

echo "<ul class=\"breadcrumb\">\n";
foreach ($path as $id => $title) {
	if ($id != $page->id) {
		printf ("<li><a href=\"/%s\">%s</a> <span class=\"divider\">/</span></li>\n", $id, $title);
	} else {
		printf ("<li class=\"active\">%s</li>\n", $title);
	}
}
echo '</ul>';

?>
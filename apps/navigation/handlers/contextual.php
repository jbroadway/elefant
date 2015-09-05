<?php

/**
 * Displays contextual navigation, opening and closing
 * sections based on the currently active page. Shows
 * All parents and children of the current page.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('navigation/contextual');
 *
 * In a template, call it like this:
 *
 *     {! navigation/contextual !}
 *
 * Also available in the dynamic objects menu as "Navigation: Contextual".
 */

$n = Link::nav ();
$path = $n->path ($page->id);
$path = ($path) ? $path : array ();

require_once ('apps/navigation/lib/Functions.php');

if (conf ('I18n', 'multilingual')) {
	$section = $n->node ($i18n->language);
	if (is_array ($section->children)) {
		navigation_print_context ($section->children, $path);
	}
} else {
	navigation_print_context ($n->tree, $path);
}

<?php

/**
 * Displays a multi-level dynamic drop menu.
 *
 * Usage:
 *
 * ### 1. Embed the drop menu in your layout file like this:
 *
 *     {! navigation/dropmenu !}
 *
 * ### 2. Customize the menu in your design stylesheet.
 *
 * See [apps/navigation/css/dropmenu.css](/apps/navigation/css/dropmenu.css)
 * for the default styles you can override.
 */

$n = Link::nav ();

$data['id'] = isset ($data['id']) ? $data['id'] : 'dropmenu';

$page->add_style ('/apps/navigation/css/dropmenu.css');
$page->add_script ('/apps/navigation/js/dropmenu.js');

require_once ('apps/navigation/lib/Functions.php');

if (conf ('I18n', 'multilingual')) {
	$section = $n->node ($i18n->language);
	if ($section === null || ! isset ($section->children)) return;
	if (is_object ($section->children)) {
		$section->children = (array) $section->children;
	}
	navigation_print_dropmenu ($section->children, $data['id']);
} else {
	navigation_print_dropmenu ($n->tree, $data['id']);
}

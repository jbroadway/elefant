<?php

/**
 * Displays a multi-level dynamic drop menu.
 *
 * Usage:
 *
 * 1. Embed the drop menu in your layout file like this:
 *
 *     {! navigation/dropmenu !}
 *
 * 2. Customize the menu in your design stylesheet.
 */

$n = new Navigation;

$data['id'] = isset ($data['id']) ? $data['id'] : 'dropmenu';

$page->add_style ('/apps/navigation/css/dropmenu.css');
$page->add_script ('/apps/navigation/js/dropmenu.js');

function navigation_print_level ($tree, $id = false) {
	if ($id) {
		echo '<ul id="' . $id . '" class="dropmenu">';
	} else {
		echo '<ul class="dropmenu">';
	}

	foreach ($tree as $item) {
		printf ('<li><a href="/%s">%s</a>', $item->attr->id, $item->data);
		if (isset ($item->children)) {
			navigation_print_level ($item->children);
		}
		echo '</li>';
	}
	echo '</ul>';
}

navigation_print_level ($n->tree, $data['id']);

$this->cache = true;

?>
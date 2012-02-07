<?php

/**
 * Displays a complete site map as a multi-level
 * bulleted list.
 */

$n = new Navigation;

function navigation_print_level ($tree) {
	echo '<ul>';
	foreach ($tree as $item) {
		printf ('<li><a href="/%s">%s</a>', $item->attr->id, $item->data);
		if (isset ($item->children)) {
			navigation_print_level ($item->children);
		}
		echo '</li>';
	}
	echo '</ul>';
}

navigation_print_level ($n->tree);

$this->cache = true;

?>
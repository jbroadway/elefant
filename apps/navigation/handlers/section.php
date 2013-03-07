<?php

/**
 * Displays a single section of the navigation as a
 * bulleted list, with `class="current"` added to
 * the current page's `<li>` element for custom styling.
 */

$n = Link::nav ();
$section = $n->node ($data['section']);

if (is_array ($section->children)) {
	echo '<ul>';
	foreach ($section->children as $item) {
		echo Link::single ($item->attr->id, $item->data);
	}
	echo '</ul>';
}	

?>
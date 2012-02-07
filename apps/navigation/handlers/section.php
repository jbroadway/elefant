<?php

/**
 * Displays a single section of the navigation as a
 * bulleted list, with `class="current"` added to
 * the current page's `<li>` element for custom styling.
 */

$n = new Navigation;
$section = $n->node ($data['section']);

echo '<ul>';
foreach ($section->children as $item) {
	if ($item->attr->id == $page->id) {
		printf ('<li class="current"><a href="/%s">%s</a></li>', $item->attr->id, $item->data);
	} else {
		printf ('<li><a href="/%s">%s</a></li>', $item->attr->id, $item->data);
	}
}
echo '</ul>';

?>
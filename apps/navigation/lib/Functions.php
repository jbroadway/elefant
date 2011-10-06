<?php

/**
 * Print the site tree as an HTML list with the current
 * context open as sub-lists up to the first-level children
 * of the current page. The currently active page will have
 * the class `current` and parents will have the class
 * `parent`.
 *
 * Usage:
 *
 *     $n = new Navigation;
 *     $path = $n->path ($page->id);
 *     $path = ($path) ? $path : array ();
 *     navigation_print_context ($n->tree, $path);
 */
function navigation_print_context ($tree, $path) {
	echo '<ul>';
	foreach ($tree as $item) {
		if ($item->attr->id == $path[count ($path) - 1]) {
			printf ('<li class="current"><a href="/%s">%s</a>', $item->attr->id, $item->data);
			if (isset ($item->children)) {
				navigation_print_context ($item->children, $path);
			}
			echo '</li>';
		} elseif (in_array ($item->attr->id, $path)) {
			printf ('<li class="parent"><a href="/%s">%s</a>', $item->attr->id, $item->data);
			if (isset ($item->children)) {
				navigation_print_context ($item->children, $path);
			}
			echo '</li>';
		} else {
			printf ('<li><a href="/%s">%s</a></li>', $item->attr->id, $item->data);
		}
	}
	echo '</ul>';
}

/**
 * Clears all memcache keys for the navigation app.
 */
function navigation_clear_cache () {
	global $memcache;
	//$memcache->delete ('navigation_top');
	$memcache->delete ('navigation_map');
}

?>
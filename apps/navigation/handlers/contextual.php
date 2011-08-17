<?php

$n = new Navigation;
$path = $n->path ($page->id);
if (! $path) {
	$path = array ();
}

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

navigation_print_context ($n->tree, $path);

?>
<?php

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
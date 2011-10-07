<?php

$res = $memcache->get ('_navigation_top');
if ($res) {
	echo str_replace (
		sprintf ('<li><a href="/%s">', $page->id),
		sprintf ('<li class="current"><a href="/%s">', $page->id),
		$res
	);
	return;
}

$n = new Navigation;

$out = '<ul>';
foreach ($n->tree as $item) {
	$out .= sprintf ('<li><a href="/%s">%s</a></li>', $item->attr->id, $item->data);
}
$out .= '</ul>';

$memcache->set ('_navigation_top', $out);

echo str_replace (
	sprintf ('<li><a href="/%s">', $page->id),
	sprintf ('<li class="current"><a href="/%s">', $page->id),
	$out
);

?>
<?php

/**
 * Returns a list of internal page links to the WYSIWYG editor's
 * link dialog.
 *
 * Note: Only includes public pages.
 */

function admin_links_sort ($a, $b) {
	if ($a['title'] == $b['title']) {
		return 0;
	}
	return ($a['title'] < $b['title']) ? -1 : 1;
}

$page->layout = false;

$menu = Webpage::query ('id, title, menu_title')
		->where ('access', 'public')
		->fetch_orig ();
$out = array ();
foreach ($menu as $pg) {
	$mt = (! empty ($pg->menu_title)) ? $pg->menu_title : $pg->title;
	$out[] = array ('url' => Link::href ($pg->id), 'title' => $mt);
}
usort ($out, 'admin_links_sort');

echo json_encode ($out);

?>
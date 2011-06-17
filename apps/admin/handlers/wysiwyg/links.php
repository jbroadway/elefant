<?php

$page->template = false;

$menu = Webpage::query ('id, title, menu_title')
		->where ('access', 'public')
		->where ('weight > -1') // negative weight leaves pages out of menus
		->order ('weight desc')
		->fetch_orig ();
$out = array ();
foreach ($menu as $pg) {
	$mt = (! empty ($pg->menu_title)) ? $pg->menu_title : $pg->title;
	$out[] = array ('url' => '/' . $pg->id, 'title' => $mt);
}

echo json_encode ($out);

?>
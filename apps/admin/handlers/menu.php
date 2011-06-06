<?php

$menu = Webpage::query ()
	->where ('weight > -1') // negative weight leaves pages out of menus
	->order ('weight desc')
	->fetch_orig ();

echo '<ul>';
foreach ($menu as $page) {
	$mt = (! empty ($page->menu_title)) ? $page->menu_title : $page->title;
	printf ('<li><a href="/%s">%s</a></li>', $page->id, $mt);
}
echo '</ul>';

?>
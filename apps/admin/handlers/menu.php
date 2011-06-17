<?php

if (! User::require_login ()) {
	$menu = Webpage::query ('id, title, menu_title')
		->where ('access', 'public')
		->where ('weight > -1') // negative weight leaves pages out of menus
		->order ('weight desc')
		->fetch_orig ();
} elseif (User::require_admin ()) {
	$menu = Webpage::query ('id, title, menu_title')
		->where ('weight > -1') // negative weight leaves pages out of menus
		->order ('weight desc')
		->fetch_orig ();
} else {
	$menu = Webpage::query ('id, title, menu_title')
		->where ('access in("public","member")')
		->where ('weight > -1') // negative weight leaves pages out of menus
		->order ('weight desc')
		->fetch_orig ();
}

echo '<ul>';
foreach ($menu as $page) {
	$mt = (! empty ($page->menu_title)) ? $page->menu_title : $page->title;
	printf ('<li><a href="/%s">%s</a></li>', $page->id, $mt);
}
echo '</ul>';

?>
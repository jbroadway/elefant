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
foreach ($menu as $pg) {
	$mt = (! empty ($pg->menu_title)) ? $pg->menu_title : $pg->title;
	printf ('<li><a href="/%s">%s</a></li>', $pg->id, $mt);
}
echo '</ul>';

?>
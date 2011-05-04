<?php

$page->layout = 'admin';

if (! simple_auth ()) {
	$page->template = 'admin/base';
	$page->title = 'Login Required';
	echo '<p>You must be logged in to access these pages.</p>';
	return;
}

$page->template = 'admin/admin';

$wp = new Webpage ();

$page->pages = $wp->query ()->order ('title asc')->fetch_assoc ('id', 'title');

?>
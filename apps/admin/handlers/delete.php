<?php

$page->layout = 'admin';

if (! simple_auth ()) {
	$page->title = 'Login Required';
	echo '<p>You must be logged in to access these pages.</p>';
	return;
}

$wp = new Webpage ($_GET['page']);

if (! $wp->remove ()) {
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $wp->error;
	return;
}

header ('Location: /admin');
$this->hook ('admin/delete', $_GET);
exit;

?>
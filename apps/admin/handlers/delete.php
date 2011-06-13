<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

$wp = new Webpage ($_GET['page']);

if (! $wp->remove ()) {
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $wp->error;
	return;
}

$this->hook ('admin/delete', $_GET);
$page->title = 'Page Deleted';
echo '<p>The page has been deleted.</p>';
echo '<p><a href="/">Continue</a></p>';

?>
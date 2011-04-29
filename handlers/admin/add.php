<?php

auth_basic ();

$page->layout = 'admin';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$wp = new Webpage ($_POST);
	$wp->put ();
	if (! $wp->error) {
		header ('Location: /admin');
		exit;
	}
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $wp->error;
} else {
	$page->template = 'admin/add';
}

?>
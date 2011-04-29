<?php

auth_basic ();

$page->layout = 'admin';

require_once ('models/Webpage.php');

$wp = new Webpage ($_GET['page']);

if (! $wp->remove ()) {
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $wp->error;
	return;
}

header ('Location: /admin');
exit;

?>
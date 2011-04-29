<?php

auth_basic ();

$page->layout = 'admin';

require_once ('models/Webpage.php');

$wp = new Webpage ($_GET['page']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	require_once ('models/Webpage.php');

	$wp = new Webpage ($_GET['page']);
	$wp->title = $_POST['title'];
	$wp->template = $_POST['template'];
	$wp->head = $_POST['head'];
	$wp->body = $_POST['body'];
	$wp->put ();
	if (! $wp->error) {
		header ('Location: /admin');
		exit;
	}
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $wp->error;
} else {
	echo $tpl->render ('admin/edit', $wp);
}

?>
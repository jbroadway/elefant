<?php

$page->layout = 'admin';
$page->template = 'admin/base';

if (! simple_auth ()) {
	$page->title = 'Login Required';
	echo '<p>You must be logged in to access these pages.</p>';
	return;
}

$wp = new Webpage ($_GET['page']);

$f = new Form ('post', 'admin/edit');
if ($f->submit ()) {
	$wp = new Webpage ($_GET['page']);
	$wp->title = $_POST['title'];
	$wp->layout = $_POST['layout'];
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
	$wp->failed = $f->failed;
	$wp = $f->merge_values ($wp);
	echo $tpl->render ('admin/edit', $wp);
}

?>
<?php

$page->layout = 'admin';
$page->template = 'admin/base';

if (! simple_auth ()) {
	$page->title = 'Login Required';
	echo '<p>You must be logged in to access these pages.</p>';
	return;
}

$f = new Form ('post', 'admin/add');
if ($f->submit ()) {
	$wp = new Webpage ($_POST);
	$wp->put ();
	if (! $wp->error) {
		header ('Location: /admin');
		$_POST['page'] = $_POST['id'];
		$this->hook ('admin/add', $_POST);
		exit;
	}
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $wp->error;
} else {
	$pg = new Page;
	$pg->layout = '';
	$pg->failed = $f->failed;
	$pg = $f->merge_values ($pg);
	echo $tpl->render ('admin/add', $pg);
}

?>
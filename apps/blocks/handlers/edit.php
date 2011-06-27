<?php

$page->layout = 'admin';
$page->template = 'admin/base';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

$b = new Blocks ($_GET['id']);

$f = new Form ('post', 'blocks/edit');
if ($f->submit ()) {
	$b->title = $_POST['title'];
	$b->body = $_POST['body'];
	$b->access = $_POST['access'];
	$b->show_title = $_POST['show_title'];
	$b->put ();
	Versions::add ($b);
	if (! $b->error) {
		$page->title = i18n_get ('Block Saved');
		echo '<p><a href="/blocks/admin">' . i18n_get ('Continue') . '</a></p>';
		$_POST['id'] = $_GET['id'];
		$this->hook ('blocks/edit', $_POST);
		return;
	}
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $b->error;
} else {
	$b->yes_no = array ('yes', 'no');
	$b->failed = $f->failed;
	$b = $f->merge_values ($b);
	$page->title = 'Edit Block: ' . $b->title;
	$page->head = $tpl->render ('blocks/edit/head', $wp)
				. $tpl->render ('admin/wysiwyg');
	echo $tpl->render ('blocks/edit', $wp);
}

?>
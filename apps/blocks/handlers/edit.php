<?php

$page->layout = 'admin';
$page->template = 'admin/base';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

$lock = new Lock ('Block', $_GET['id']);
if ($lock->exists ()) {
	$page->title = i18n_get ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
} else {
	$lock->add ();
}

$b = new Block ($_GET['id']);

$f = new Form ('post', 'blocks/edit');
if ($f->submit ()) {
	$b->title = $_POST['title'];
	$b->body = $_POST['body'];
	$b->access = $_POST['access'];
	$b->show_title = $_POST['show_title'];
	$b->put ();
	Versions::add ($b);
	if (! $b->error) {
		if (isset ($_GET['return'])) {
			header ('Location: ' . $_GET['return']);
			$_POST['id'] = $_GET['id'];
			$lock->remove ();
			$this->hook ('blocks/edit', $_POST);
			exit;
		}
		$page->title = i18n_get ('Block Saved');
		echo '<p><a href="/blocks/admin">' . i18n_get ('Continue') . '</a></p>';
		$_POST['id'] = $_GET['id'];
		$lock->remove ();
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
	$page->head = $tpl->render ('blocks/edit/head', $b)
				. $tpl->render ('admin/wysiwyg');
	echo $tpl->render ('blocks/edit', $b);
}

?>
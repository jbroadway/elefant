<?php

/**
 * Block edit form.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
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
$f->verify_csrf = false;
if ($f->submit ()) {
	$b->title = $_POST['title'];
	$b->body = $_POST['body'];
	$b->access = $_POST['access'];
	$b->show_title = $_POST['show_title'];
	$b->put ();
	Versions::add ($b);
	if (! $b->error) {
		$this->add_notification ('Block saved.');
		$_POST['id'] = $_GET['id'];
		$lock->remove ();
		$this->hook ('blocks/edit', $_POST);
		if (isset ($_GET['return'])) {
			$this->redirect ($_GET['return']);
		}
		$this->redirect ('/blocks/admin');
	}
	$page->title = i18n_get ('An Error Occurred');
	echo i18n_get ('Error Message') . ': ' . $b->error;
} else {
	$b->yes_no = array ('yes', 'no');
	$b->failed = $f->failed;
	$b = $f->merge_values ($b);
	$page->title = i18n_get ('Edit Block') . ': ' . $b->title;
	$page->head = $tpl->render ('blocks/edit/head', $b)
				. $tpl->render ('admin/wysiwyg');
	echo $tpl->render ('blocks/edit', $b);
}

?>
<?php

/**
 * Block edit form.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'blocks');

$lock = new Lock ('Block', $_GET['id']);
if ($lock->exists ()) {
	$page->title = __ ('Editing Locked');
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
		$this->add_notification (__ ('Block saved.'));
		$_POST['id'] = $_GET['id'];
		$lock->remove ();
		$this->hook ('blocks/edit', $_POST);
		if (isset ($_GET['return'])) {
			$this->redirect ($_GET['return']);
		}
		$this->redirect ('/blocks/admin');
	}
	$page->title = __ ('An Error Occurred');
	echo __ ('Error Message') . ': ' . $b->error;
} else {
	$b->yes_no = array ('yes' => __ ('Yes'), 'no' => __ ('No'));
	$b->failed = $f->failed;
	$b = $f->merge_values ($b);
	$page->title = __ ('Edit Block') . ': ' . $b->title;
	$this->run ('admin/util/wysiwyg');
	echo $tpl->render ('blocks/edit/head', $b);
	echo $tpl->render ('blocks/edit', $b);
}

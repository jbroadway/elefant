<?php

$page->layout = 'admin';
$page->template = 'admin/base';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

$f = new Form ('post', 'admin/add');
if ($f->submit ()) {
	$wp = new Webpage ($_POST);
	$wp->put ();
	Versions::add ($wp);
	if (! $wp->error) {
		header ('Location: /' . $_POST['id']);
		$_POST['page'] = $_POST['id'];
		$this->hook ('admin/add', $_POST);
		exit;
	}
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $wp->error;
} else {
	$pg = new Page;
	$pg->layout = 'default';
	$pg->weight = '0';

	$pg->layouts = array ();
	$d = dir (getcwd () . '/layouts');
	while (false != ($entry = $d->read ())) {
		if (preg_match ('/^(.*)\.html$/', $entry, $regs)) {
			$pg->layouts[] = $regs[1];
		}
	}
	$d->close ();

	$pg->failed = $f->failed;
	$pg = $f->merge_values ($pg);
	$page->title = 'Add Page';
	$page->head = $tpl->render ('admin/add/head')
				. $tpl->render ('admin/wysiwyg');
	echo $tpl->render ('admin/add', $pg);
}

?>
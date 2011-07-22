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
		$this->add_notification (i18n_get ('Page created.'));
		$_POST['page'] = $_POST['id'];
		$this->hook ('admin/add', $_POST);
		$this->redirect ('/' . $_POST['id']);
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
	$page->head = $tpl->render ('admin/add/head', $pg)
				. $tpl->render ('admin/wysiwyg');
	echo $tpl->render ('admin/add', $pg);
}

?>
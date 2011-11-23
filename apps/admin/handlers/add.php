<?php

/**
 * The page add form.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$f = new Form ('post', 'admin/add');
$f->verify_csrf = false;
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
	$page->title = i18n_get ('An Error Occurred');
	echo i18n_get ('Error Message') . ': ' . $wp->error;
} else {
	$pg = new Page;
	$pg->layout = 'default';
	$pg->weight = '0';

	$layouts = array ();
	$d = dir (getcwd () . '/layouts');
	while (false != ($entry = $d->read ())) {
		if (preg_match ('/^(.*)\.html$/', $entry, $regs)) {
			$layouts[] = $regs[1];
		}
	}
	$d->close ();
	sort ($layouts);
	$pg->layouts = $layouts;

	$pg->failed = $f->failed;
	$pg = $f->merge_values ($pg);
	$page->title = i18n_get ('Add Page');
	$page->head = $tpl->render ('admin/add/head', $pg)
				. $tpl->render ('admin/wysiwyg');
	echo $tpl->render ('admin/add', $pg);
}

?>
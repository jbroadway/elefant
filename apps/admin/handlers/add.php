<?php

/**
 * The page add form.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

require_once ('apps/admin/lib/Functions.php');

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
	$pg->layouts = admin_get_layouts ();
	$pg->failed = $f->failed;
	$pg = $f->merge_values ($pg);
	$page->title = i18n_get ('Add Page');
	$this->run ('admin/util/wysiwyg');
	echo $tpl->render ('admin/add/head', $pg);
	echo $tpl->render ('admin/add', $pg);
}

?>
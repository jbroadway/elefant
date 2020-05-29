<?php

/**
 * The page add form.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'admin/pages', 'admin/add');

$f = new Form ('post', 'admin/add');

if ($f->submit ()) {
	unset ($_POST['_token_']);
	$wp = new Webpage ($_POST);
	$wp->put ();
	Versions::add ($wp);
	if (! $wp->error) {
		$this->add_notification (__ ('Page created.'));
		$_POST['page'] = $_POST['id'];
		$this->hook ('admin/add', $_POST);
		$this->redirect ('/' . $_POST['id']);
	}
	$page->title = __ ('An Error Occurred');
	echo __ ('Error Message') . ': ' . $wp->error;
} else {
	$pg = new Page;
	$pg->layout = 'default';
	$pg->thumbnail = '';
	$pg->access = 'public';
	$pg->weight = '0';
	$pg->failed = $f->failed;
	$pg = $f->merge_values ($pg);
	$page->window_title = __ ('Add Page');
	$this->run ('admin/util/wysiwyg');
	echo $tpl->render ('admin/add/head', $pg);
	echo $tpl->render ('admin/add', $pg);
}

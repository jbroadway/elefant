<?php

/**
 * The page edit form.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'admin/pages', 'admin/edit');

$lock = new Lock ('Webpage', $_GET['page']);
if ($lock->exists ()) {
	$page->title = __ ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
} else {
	$lock->add ();
}

$wp = new Webpage ($_GET['page']);

$f = new Form ('post', 'admin/edit');

if ($f->submit ()) {
	$wp->id = $_POST['id'];
	$wp->title = $_POST['title'];
	$wp->menu_title = $_POST['menu_title'];
	$wp->window_title = $_POST['window_title'];
	$wp->access = $_POST['access'];
	$wp->layout = $_POST['layout'];
	$wp->description = $_POST['description'];
	$wp->keywords = $_POST['keywords'];
	$wp->body = $_POST['body'];
	$wp->thumbnail = $_POST['thumbnail'];
	$wp->update_extended ();
	$wp->put ();
	if (! $wp->error) {
		Versions::add ($wp);
		$cache->delete ('_admin_page_' . $_GET['page']);
		$this->add_notification (__ ('Page saved.'));
		$_POST['page'] = $_GET['page'];
		$lock->remove ();
		$this->hook ('admin/edit', $_POST);
		if (isset ($_GET['redirect']) && $_GET['redirect'] == 'admin') {
			$this->redirect ('/admin/pages');
		} else {
			$this->redirect ('/' . $_POST['id']);
		}
	}
	$page->title = __ ('An Error Occurred');
	echo __ ('Error Message') . ': ' . $wp->error;
} else {
	$wp->failed = $f->failed;
	$wp = $f->merge_values ($wp);
	$page->window_title = __ ('Edit Page') . ': ' . Template::sanitize ($wp->title);
	$this->run ('admin/util/wysiwyg');
	echo $tpl->render ('admin/edit/head', $wp);
	echo $tpl->render ('admin/edit', $wp);
}

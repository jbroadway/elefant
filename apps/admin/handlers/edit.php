<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$lock = new Lock ('Webpage', $_GET['page']);
if ($lock->exists ()) {
	$page->title = i18n_get ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
} else {
	$lock->add ();
}

$wp = new Webpage ($_GET['page']);

$f = new Form ('post', 'admin/edit');
$f->verify_csrf = false;
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
	$wp->put ();
	if (! $wp->error) {
		Versions::add ($wp);
		$memcache->delete ('_admin_page_' . $_GET['page']);
		$this->add_notification (i18n_get ('Page saved.'));
		$_POST['page'] = $_GET['page'];
		$lock->remove ();
		$this->hook ('admin/edit', $_POST);
		$this->redirect ('/' . $_POST['id']);
	}
	$page->title = i18n_get ('An Error Occurred');
	echo i18n_get ('Error Message') . ': ' . $wp->error;
} else {
	$layouts = array ();
	$d = dir (getcwd () . '/layouts');
	while (false != ($entry = $d->read ())) {
		if (preg_match ('/^(.*)\.html$/', $entry, $regs)) {
			$layouts[] = $regs[1];
		}
	}
	$d->close ();
	sort ($layouts);
	$wp->layouts = $layouts;

	$wp->failed = $f->failed;
	$wp = $f->merge_values ($wp);
	$page->title = i18n_get ('Edit Page') . ': ' . $wp->title;
	$page->head = $tpl->render ('admin/edit/head', $wp)
				. $tpl->render ('admin/wysiwyg');
	echo $tpl->render ('admin/edit', $wp);
}

?>
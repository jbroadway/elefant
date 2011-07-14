<?php

$page->layout = 'admin';
$page->template = 'admin/base';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

$wp = new Webpage ($_GET['page']);

$f = new Form ('post', 'admin/edit');
if ($f->submit ()) {
	$wp->id = $_POST['id'];
	$wp->title = $_POST['title'];
	$wp->menu_title = $_POST['menu_title'];
	$wp->window_title = $_POST['window_title'];
	$wp->weight = $_POST['weight'];
	$wp->access = $_POST['access'];
	$wp->layout = $_POST['layout'];
	$wp->description = $_POST['description'];
	$wp->keywords = $_POST['keywords'];
	$wp->body = $_POST['body'];
	$wp->put ();
	if (! $wp->error) {
		Versions::add ($wp);
		header ('Location: /' . $_POST['id']);
		$_POST['page'] = $_GET['page'];
		$this->hook ('admin/edit', $_POST);
		exit;
	}
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $wp->error;
} else {
	$wp->layouts = array ();
	$d = dir (getcwd () . '/layouts');
	while (false != ($entry = $d->read ())) {
		if (preg_match ('/^(.*)\.html$/', $entry, $regs)) {
			$wp->data['layouts'][] = $regs[1];
		}
	}
	$d->close ();

	$wp->failed = $f->failed;
	$wp = $f->merge_values ($wp);
	$page->title = 'Edit Page: ' . $wp->title;
	$page->head = $tpl->render ('admin/edit/head', $wp)
				. $tpl->render ('admin/wysiwyg');
	echo $tpl->render ('admin/edit', $wp);
}

?>
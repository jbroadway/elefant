<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$lock = new Lock ('Blog', $_GET['id']);
if ($lock->exists ()) {
	$page->title = i18n_get ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
}

require_once ('apps/blog/lib/Filters.php');

$p = new blog\Post ($_GET['id']);
$tags = $p->tags;
$title = $p->title;

if (! $p->remove ()) {
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $u->error;
	return;
}

db_execute ('delete from blog_post_tag where post_id = ?', $_GET['id']);

$_GET['page'] = 'blog/post/' . $_GET['id'] . '/' . blog_filter_title ($title);
$this->hook ('blog/delete', $_GET);
$this->add_notification (i18n_get ('Blog post deleted.'));
$this->redirect ('/blog/admin');

?>
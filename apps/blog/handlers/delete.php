<?php

/**
 * Blog post delete handler.
 */

$this->require_acl ('admin', 'admin/delete', 'blog');

$page->layout = 'admin';

if (! isset ($_POST['id'])) {
	$this->redirect ('/blog/admin');
}

$lock = new Lock ('Blog', $_POST['id']);
if ($lock->exists ()) {
	$page->title = __ ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
}

require_once ('apps/blog/lib/Filters.php');

$p = new blog\Post ($_POST['id']);
$tags = $p->tags;
$title = $p->title;

$_POST = array_merge ($_POST, (array) $p->orig ());

if (! $p->remove ()) {
	$page->title = __ ('An Error Occurred');
	echo __ ('Error Message') . ': ' . $u->error;
	return;
}

// reset blog rss cache
$cache->delete ('blog_rss');

DB::execute ('delete from #prefix#blog_post_tag where post_id = ?', $_POST['id']);

$_POST['page'] = 'blog/post/' . $_POST['id'] . '/' . URLify::filter ($title);
$_POST['url'] = '/' . $_POST['page'];
$this->hook ('blog/delete', $_POST);
$this->add_notification (__ ('Blog post deleted.'));
$this->redirect ('/blog/admin');

?>
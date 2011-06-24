<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
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
$page->title = 'Blog Post Deleted';
echo '<p><a href="/blog/admin">Continue</a></p>';

?>
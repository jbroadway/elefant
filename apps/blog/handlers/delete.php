<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

$p = new blog\Post ($_GET['id']);
$tags = $p->tags;

if (! $p->remove ()) {
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $u->error;
	return;
}

db_execute ('delete from blog_post_tag where post_id = ?', $_GET['id']);

$this->hook ('blog/delete', $_GET);
$page->title = 'Blog Post Deleted';
echo '<p><a href="/blog/admin">Continue</a></p>';

?>
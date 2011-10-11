<?php

/**
 * Admin page where you can edit posts and create new ones.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

require_once ('apps/blog/lib/Filters.php');

$limit = 20;
$_GET['offset'] = (isset ($_GET['offset'])) ? $_GET['offset'] : 0;

$lock = new Lock ();

$posts = blog\Post::query ('id, title, ts, author, published')
	->order ('ts desc')
	->fetch_orig ($limit, $_GET['offset']);
$count = blog\Post::query ()->count ();

foreach ($posts as $k => $p) {
	$posts[$k]->locked = $lock->exists ('Blog', $p->id);
}

$page->title = i18n_get ('Blog Posts');
echo $tpl->render ('blog/admin', array (
	'posts' => $posts,
	'count' => $count,
	'offset' => $_GET['offset'],
	'more' => ($count > $_GET['offset'] + $limit) ? true : false,
	'prev' => $_GET['offset'] - $limit,
	'next' => $_GET['offset'] + $limit
));

?>
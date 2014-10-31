<?php

/**
 * Admin page where you can edit posts and create new ones.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'blog');

require_once ('apps/blog/lib/Filters.php');

$limit = 20;
$num = isset ($_GET['offset']) ? $_GET['offset'] : 1;
$offset = ($num - 1) * $limit;

$lock = new Lock ();

$posts = blog\Post::query ('id, title, ts, author, published')
	->order ('ts desc')
	->fetch_orig ($limit, $offset);
$count = blog\Post::query ()->count ();

foreach ($posts as $k => $p) {
	$posts[$k]->locked = $lock->exists ('Blog', $p->id);
}

$page->title = __ ('Blog Posts');
echo $tpl->render ('blog/admin', array (
	'limit' => $limit,
	'total' => $count,
	'posts' => $posts,
	'count' => count ($posts),
	'url' => '/blog/admin?offset=%d'
));

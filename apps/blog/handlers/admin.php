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
$q = isset ($_GET['q']) ? $_GET['q'] : ''; // search query
$q_fields = array ('title', 'author', 'tags', 'body');
$q_exact = array ('author', 'published');
$url = '/blog/admin?q=' . urlencode ($q) . '&offset=%d';

$lock = new Lock ();

$posts = blog\Post::query ('id, title, ts, author, published, tags')
	->where_search ($q, $q_fields, $q_exact)
	->order ('ts desc')
	->fetch_orig ($limit, $offset);

$count = blog\Post::query ()
	->where_search ($q, $q_fields, $q_exact)
	->count ();

foreach ($posts as $k => $p) {
	$posts[$k]->locked = $lock->exists ('Blog', $p->id);
	$posts[$k]->tags = preg_split ('/, ?/', $posts[$k]->tags);
}

$page->title = __ ('Blog Posts');
echo $tpl->render ('blog/admin', array (
	'limit' => $limit,
	'total' => $count,
	'posts' => $posts,
	'count' => count ($posts),
	'url' => $url,
	'q' => $q
));

<?php

require_once ('apps/blog/lib/Filters.php');

$page->layout = false;
header ('Content-Type: text/xml');
$p = new blog\Post;
$page->posts = $p->latest (10, 0);
$page->title = $appconf['Blog']['title'];
$page->date = gmdate ('Y-m-d\TH:i:s');
foreach ($page->posts as $k => $post) {
	$page->posts[$k]->url = '/blog/post/' . $post->id . '/' . blog_filter_title ($post->title);
}

echo $tpl->render ('blog/rss', $page);

?>
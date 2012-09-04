<?php

/**
 * Renders the RSS feed for the blog.
 */

$res = $cache->get ('_blog_rss');
if (! $res) {
	require_once ('apps/blog/lib/Filters.php');

	$p = new blog\Post;
	$page->posts = $p->latest (10, 0);
	$page->title = $appconf['Blog']['title'];
	$page->date = gmdate ('Y-m-d\TH:i:s');
	foreach ($page->posts as $k => $post) {
		$page->posts[$k]->url = '/blog/post/' . $post->id . '/' . URLify::filter ($post->title);
	}

	$res = $tpl->render ('blog/rss', $page);
	$cache->set ('_blog_rss', $res, 1800); // half an hour
}
$page->layout = FALSE;
header ('Content-Type: text/xml');
echo $res;

?>

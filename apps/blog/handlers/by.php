<?php

/**
 * Displays a list of blog posts by author.
 */

$page->layout = $appconf['Blog']['layout'];

require_once ('apps/blog/lib/Filters.php');

$page->limit = 10;
$page->author = urldecode ($this->params[0]);
if (! $page->author) {
	header ('Location: /blog');
	exit;
}
$page->num = (count ($this->params) > 1 && is_numeric ($this->params[1])) ? $this->params[1] - 1 : 0;
$page->offset = $page->num * $page->limit;

$p = new blog\Post;
$posts = $p->by ($page->author, $page->limit, $page->offset);
$page->count = $p->query ()->where ('published', 'yes')->where ('author', $page->author)->count ();
$page->last = $page->offset + count ($posts);
$page->more = ($page->count > $page->last) ? true : false;
$page->next = $page->num + 2;

foreach ($posts as $post) {
	$post->url = '/blog/post/' . $post->id . '/' . blog_filter_title ($post->title);
	$post->tag_list = explode (',', $post->tags);
	echo $tpl->render ('blog/post', $post);
}

$page->title = i18n_getf ('Posts by %s', $tpl->sanitize ($page->author));

$page->add_script (sprintf (
	'<link rel="alternate" type="application/rss+xml" href="http://%s/blog/rss" />',
	$_SERVER['HTTP_HOST']
));

echo $tpl->render ('blog/by', $page);

?>
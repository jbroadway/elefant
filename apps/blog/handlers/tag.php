<?php

$page->layout = $appconf['Blog']['layout'];

require_once ('apps/blog/lib/Filters.php');

$page->limit = 10;
$page->tag = $this->params[0];
if (! $page->tag) {
	header ('Location: /blog');
	exit;
}
$page->num = (count ($this->params) > 1 && is_numeric ($this->params[1])) ? $this->params[1] - 1 : 0;
$page->offset = $page->num * $page->limit;

$p = new blog\Post;
$posts = $p->tagged ($page->tag, $page->limit, $page->offset);
$page->count = $p->count_by_tag ($page->tag);
$page->last = $page->offset + count ($posts);
$page->more = ($page->count > $page->last) ? true : false;
$page->next = $page->num + 2;

foreach ($posts as $post) {
	$post->url = '/blog/post/' . $post->id . '/' . blog_filter_title ($post->title);
	$post->tag_list = explode (',', $post->tags);
	echo $tpl->render ('blog/post', $post);
}

$page->title = i18n_getf ('Tagged: %s', $tpl->sanitize ($page->tag));

$page->template = 'blog/tag';

$page->add_script (sprintf (
	'<link rel="alternate" type="application/rss+xml" href="http://%s/blog/rss" />',
	$_SERVER['HTTP_HOST']
));

?>
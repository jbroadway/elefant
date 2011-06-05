<?php

$page->layout = $appconf['Blog']['layout'];

require_once ('apps/blog/lib/Filters.php');

$page->limit = 10;
$page->num = (count ($this->params) > 0 && is_numeric ($this->params[0])) ? $this->params[0] - 1 : 0;
$page->offset = $page->num * $page->limit;

$p = new blog\Post;
$posts = $p->latest ($page->limit, $page->offset);
$page->count = $p->query ()->count ();
$page->last = $page->offset + count ($posts);
$page->more = ($page->count > $page->last) ? true : false;
$page->next = $page->num + 2;

foreach ($posts as $post) {
	$post->url = '/blog/post/' . $post->id . '/' . blog_filter_title ($post->title);
	echo $tpl->render ('blog/post', $post);
}

if (! $this->internal) {
	$page->title = $appconf['Blog']['title'];
}

$page->template = 'blog/index';

?>
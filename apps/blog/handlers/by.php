<?php

require_once ('apps/blog/lib/Filters.php');

$page->limit = 10;
$page->author = $this->params[0];
if (! $page->author) {
	header ('Location: /blog');
	exit;
}
$page->num = (count ($this->params) > 1 && is_numeric ($this->params[1])) ? $this->params[1] - 1 : 0;
$page->offset = $page->num * $page->limit;

$p = new blog\Post;
$posts = $p->by ($page->author, $page->limit, $page->offset);
$page->count = $p->query ()->count ();
$page->last = $page->offset + count ($posts);
$page->more = ($page->count > $page->last) ? true : false;
$page->next = $page->num + 2;

foreach ($posts as $post) {
	echo $tpl->render ('blog/post', $post);
}

$page->template = 'blog/by';

?>
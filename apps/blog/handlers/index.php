<?php

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
	echo $tpl->render ('blog/post', $post);
}

$page->template = 'blog/index';

?>
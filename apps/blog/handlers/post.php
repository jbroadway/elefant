<?php

$page->layout = $appconf['Blog']['post_layout'];

require_once ('apps/blog/lib/Filters.php');

$p = new blog\Post ($this->params[0]);

$page->title = $appconf['Blog']['title'];

$post = $p->orig ();
$post->full = true;
$post->url = '/blog/post/' . $post->id . '/' . blog_filter_title ($post->title);

echo $tpl->render ('blog/post', $post);

switch ($appconf['Blog']['comments']) {
	case 'facebook':
		echo $this->run ('social/facebook/comments', $post);
		break;
}

?>
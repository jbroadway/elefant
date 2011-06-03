<?php

$appconf = parse_ini_file ('apps/blog/conf/config.php', true);

$page->layout = $appconf['blog_post_layout'];

require_once ('apps/blog/lib/Filters.php');

$p = new blog\Post ($this->params[0]);

$page->title = $appconf['blog_title'];

$post = $p->orig ();
$post->full = true;
$post->url = '/blog/post/' . $post->id . '/' . blog_filter_title ($post->title);

echo $tpl->render ('blog/post', $post);

switch ($appconf['blog_comments']) {
	case 'facebook':
		echo $this->run ('social/facebook/comments', $post);
		break;
}

?>
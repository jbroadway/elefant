<?php

/**
 * Displays a single blog post.
 */

// Check for a custom handler override
$res = $this->override ('blog/post');
if ($res) { echo $res; return; }

$page->id = 'blog';
$page->layout = Appconf::blog ('Blog', 'post_layout');

require_once ('apps/blog/lib/Filters.php');

$p = new blog\Post ($this->params[0]);

$page->title = Appconf::blog ('Blog', 'title');

$post = $p->orig ();
$post->full = true;
$post->url = '/blog/post/' . $post->id . '/' . URLify::filter ($post->title);
$post->tag_list = explode (',', $post->tags);
$post->body = $tpl->run_includes ($post->body);
$post->social_buttons = Appconf::blog ('Social Buttons');

echo $tpl->render ('blog/post', $post);

switch (Appconf::blog ('Blog', 'comments')) {
	case 'disqus':
		echo $this->run ('blog/disqus/comments', $post);
		break;
	case 'facebook':
		echo $this->run ('social/facebook/comments', $post);
		break;
	default:
		if (Appconf::blog ('Blog', 'comments') !== false) {
			echo $this->run (
				Appconf::blog ('Blog', 'comments'),
				array (
					'identifier' => $post->url
				)
			);
		}
		break;
}

$page->add_script (sprintf (
	'<link rel="alternate" type="application/rss+xml" href="http://%s/blog/rss" />',
	$_SERVER['HTTP_HOST']
));

?>
<?php

/**
 * Displays a single blog post.
 */

if ($appconf['Custom Handlers']['blog/post'] != 'blog/post') {
	if (! $appconf['Custom Handlers']['blog/post']) {
		echo $this->error (404, i18n_get ('Not found'), i18n_get ('The page you requested could not be found.'));
		return;
	}
	echo $this->run ($appconf['Custom Handlers']['blog/post'] . '/' . $this->params[0], $data);
	return;
}

$page->layout = $appconf['Blog']['post_layout'];

require_once ('apps/blog/lib/Filters.php');

$p = new blog\Post ($this->params[0]);

$page->title = $appconf['Blog']['title'];

$post = $p->orig ();
$post->full = true;
$post->url = '/blog/post/' . $post->id . '/' . URLify::filter ($post->title);
$post->tag_list = explode (',', $post->tags);
$post->body = $tpl->run_includes ($post->body);
$post->social_buttons = $appconf['Social Buttons'];
foreach ($p->ext () as $k => $v) {
	$post->{$k} = $v;
}

echo $tpl->render ('blog/post', $post);

switch ($appconf['Blog']['comments']) {
	case 'disqus':
		echo $this->run ('blog/disqus/comments', $post);
		break;
	case 'facebook':
		echo $this->run ('social/facebook/comments', $post);
		break;
}

$page->add_script (sprintf (
	'<link rel="alternate" type="application/rss+xml" href="http://%s/blog/rss" />',
	$_SERVER['HTTP_HOST']
));

?>
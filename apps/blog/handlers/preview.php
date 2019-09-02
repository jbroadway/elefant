<?php

/**
 * Creates a preview of a web page based on POST data sent to it.
 * POST data must match values available to the Page object.
 */

$this->require_admin ();

$post = new blog\Post ($_POST);

$page->id = 'blog';
$page->layout = Appconf::blog ('Blog', 'post_layout');

if (Appconf::blog ('Blog', 'post_format') === 'html') {
	$post->body = $tpl->run_includes ($post->body);
} else {
	require_once ('apps/blog/lib/markdown.php');
	$post->body = $tpl->run_includes (Markdown ($post->body));
}
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
		if (Appconf::blog ('Blog', 'comments') != false) {
			echo $this->run (
				Appconf::blog ('Blog', 'comments'),
				array (
					'identifier' => $post->url
				)
			);
		}
		break;
}

$protocol = $this->is_https () ? 'https' : 'http';
$domain = Appconf::admin ('Site Settings', 'site_domain');

// add rss + jsonfeed discovery
$page->add_script (sprintf (
	'<link rel="alternate" type="application/rss+xml" href="%s://%s/blog/rss" />',
	$protocol,
	$domain
));

$page->add_script (sprintf (
	'<link rel="alternate" type="application/json" href="%s://%s/blog/feed.json" />',
	$protocol,
	$domain
));

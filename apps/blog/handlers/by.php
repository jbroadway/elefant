<?php

/**
 * Displays a list of blog posts by author.
 */

$page->id = 'blog';
$page->layout = $appconf['Blog']['layout'];

require_once ('apps/blog/lib/Filters.php');

$preview_chars = (int) Appconf::blog('Blog', 'preview_chars') ? (int) Appconf::blog('Blog', 'preview_chars') : false;

$page->limit = 10;
$page->author = urldecode ($this->params[0]);
if (! $page->author) {
	$this->redirect ('/blog');
}
$page->num = (count ($this->params) > 1 && is_numeric ($this->params[1])) ? $this->params[1] - 1 : 0;
$page->offset = $page->num * $page->limit;

$p = new blog\Post;
$posts = $p->by ($page->author, $page->limit, $page->offset);
$page->count = $p->query ()->where ('published', 'yes')->where ('author', $page->author)->count ();
$page->last = $page->offset + count ($posts);
$page->more = ($page->count > $page->last) ? true : false;
$page->next = $page->num + 2;

$footer = Appconf::blog ('Blog', 'post_footer');
$footer_stripped = strip_tags ($footer);
$footer = ($footer && ! empty ($footer_stripped))
	? $tpl->run_includes ($footer)
	: false;

if (Appconf::blog ('Blog', 'post_format') === 'markdown') {
	require_once ('apps/blog/lib/markdown.php');
}

foreach ($posts as $post) {
	if ($post->slug == '') {
		$post->slug = URLify::filter ($post->title);
		$post->put ();
	}
	
	$post->url = '/blog/post/' . $post->id . '/' . $post->slug;
	$post->fullurl = $post->url;
	$post->tag_list = (strlen ($post->tags) > 0) ? explode (',', $post->tags) : array ();
	$post->social_buttons = $appconf['Social Buttons'];
	if (Appconf::blog ('Blog', 'post_format') === 'html') {
		$post->body = $tpl->run_includes ($post->body);
	} else {
		$post->body = $tpl->run_includes (Markdown ($post->body));
	}
	if ($preview_chars) {
		$post->body = blog_filter_truncate ($post->body, $preview_chars)
			. ' <a href="' . $post->url . '">' . __ ('Read more') . '</a>';
	} else {
		$post->footer = $footer;
	}
	echo $tpl->render ('blog/post', $post);
}

$page->title = __ ('Posts by %s', $tpl->sanitize ($page->author));

$protocol = $this->is_https () ? 'https' : 'http';
$domain = conf ('General', 'site_domain');

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

echo $tpl->render ('blog/by', $page);

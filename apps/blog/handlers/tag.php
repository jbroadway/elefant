<?php

/**
 * Displays a list of blog posts by tag.
 */

$page->id = 'blog';
$page->layout = $appconf['Blog']['layout'];

require_once ('apps/blog/lib/Filters.php');

$preview_chars = (int) Appconf::blog('Blog', 'preview_chars') ? (int) Appconf::blog('Blog', 'preview_chars') : false;

$page->limit = 10;
$page->tag = urldecode ($this->params[0]);
if (! $page->tag) {
	$this->redirect ('/blog');
}
$page->num = (count ($this->params) > 1 && is_numeric ($this->params[1])) ? $this->params[1] - 1 : 0;
$page->offset = $page->num * $page->limit;

$p = new blog\Post;
$posts = $p->tagged ($page->tag, $page->limit, $page->offset);
$page->count = $p->count_by_tag ($page->tag);
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
	$post->url = '/blog/post/' . $post->id . '/' . URLify::filter ($post->title);
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

$page->title = __ ('Tagged: %s', $tpl->sanitize ($page->tag));

$page->add_script (sprintf (
	'<link rel="alternate" type="application/rss+xml" href="http://%s/blog/rss" />',
	$_SERVER['HTTP_HOST']
));

echo $tpl->render ('blog/tag', $page);

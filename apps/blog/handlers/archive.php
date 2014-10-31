<?php

/**
 * Displays a month of blog posts from the archive.
 */

$page->id = 'blog';
$page->layout = $appconf['Blog']['layout'];

require_once ('apps/blog/lib/Filters.php');

$preview_chars = (int) Appconf::blog('Blog', 'preview_chars') ? (int) Appconf::blog('Blog', 'preview_chars') : false;

if (! isset ($this->params[1])) {
	$this->redirect ('/blog');
}
$page->limit = 10;
$year = urldecode ($this->params[0]);
$month = urldecode ($this->params[1]);
$page->num = (count ($this->params) > 2 && is_numeric ($this->params[2])) ? $this->params[2] - 1 : 0;
$page->offset = $page->num * $page->limit;

$p = new blog\Post;
$posts = $p->archive ($year, $month, $page->limit, $page->offset);
$page->count = $p->count_by_month ($year, $month, $page->limit, $page->offset);
$page->last = $page->offset + count ($posts);
$page->more = ($page->count > $page->last) ? true : false;
$page->next = $page->num + 2;

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
	}
	echo $tpl->render ('blog/post', $post);
}

$months = explode (
	' ',
	__ ('January February March April May June July August September October November December')
);

$page->title = $months[$month - 1] . ' ' . $tpl->sanitize ($year);

$page->add_script (sprintf (
	'<link rel="alternate" type="application/rss+xml" href="http://%s/blog/rss" />',
	$_SERVER['HTTP_HOST']
));

echo $tpl->render ('blog/archive', $page);

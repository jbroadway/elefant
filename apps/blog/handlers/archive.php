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
$page->year = $year;
$page->month = str_pad ($month, 2, '0', STR_PAD_LEFT);

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
	}
	
	$post->url = '/blog/post/' . $post->id . '/';
	$post->fullurl = $post->url . $post->slug;
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

$months = explode (
	' ',
	__ ('January February March April May June July August September October November December')
);

$page->title = $months[$month - 1] . ' ' . $tpl->sanitize ($year);

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

echo $tpl->render ('blog/archive', $page);

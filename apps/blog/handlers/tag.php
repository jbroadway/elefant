<?php

/**
 * Displays a list of blog posts by tag.
 */

$page->layout = $appconf['Blog']['layout'];

require_once ('apps/blog/lib/Filters.php');

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

foreach ($posts as $post) {
	$post->url = '/blog/post/' . $post->id . '/' . URLify::filter ($post->title);
	$post->tag_list = explode (',', $post->tags);
	$post->social_buttons = $appconf['Social Buttons'];
	$post->body = $tpl->run_includes ($post->body);
	echo $tpl->render ('blog/post', $post);
}

$page->title = __ ('Tagged: %s', $tpl->sanitize ($page->tag));

$page->add_script (sprintf (
	'<link rel="alternate" type="application/rss+xml" href="http://%s/blog/rss" />',
	$_SERVER['HTTP_HOST']
));

echo $tpl->render ('blog/tag', $page);

?>
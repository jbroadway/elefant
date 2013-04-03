<?php

/**
 * Displays the main blog page.
 */

if ($appconf['Custom Handlers']['blog/index'] != 'blog/index') {
	if (! $appconf['Custom Handlers']['blog/index']) {
		echo $this->error (404, __ ('Not found'), __ ('The page you requested could not be found.'));
		return;
	}
	$extra = (count ($this->params) > 0) ? '/' . $this->params[0] : '';
	echo $this->run ($appconf['Custom Handlers']['blog/index'] . $extra, $data);
	return;
}

$page->id = 'blog';
$page->layout = $appconf['Blog']['layout'];

require_once ('apps/blog/lib/Filters.php');

$page->limit = 10;
$page->num = (count ($this->params) > 0 && is_numeric ($this->params[0])) ? $this->params[0] - 1 : 0;
$page->offset = $page->num * $page->limit;

$p = new blog\Post;
$posts = $p->latest ($page->limit, $page->offset);
$page->count = $p->query ()->where ('published', 'yes')->count ();
$page->last = $page->offset + count ($posts);
$page->more = ($page->count > $page->last) ? true : false;
$page->next = $page->num + 2;

if (! is_array ($posts) || count ($posts) === 0) {
	echo '<p>' . __ ('No posts yet... :(') . '</p>';
	if (User::require_admin ()) {
		echo '<p><a href="/blog/add">' . __ ('Add Blog Post') . '</a></p>';
	}
} else {
	if (User::require_admin ()) {
		echo '<p><a href="/blog/add">' . __ ('Add Blog Post') . '</a></p>';
	}

	foreach ($posts as $post) {
		$post->url = '/blog/post/' . $post->id . '/' . URLify::filter ($post->title);
		$post->tag_list = explode (',', $post->tags);
		$post->social_buttons = $appconf['Social Buttons'];
		$post->body = $tpl->run_includes ($post->body);
		echo $tpl->render ('blog/post', $post);
	}
}

if (! $this->internal) {
	$page->title = $appconf['Blog']['title'];
}

$page->add_script (sprintf (
	'<link rel="alternate" type="application/rss+xml" href="http://%s/blog/rss" />',
	$_SERVER['HTTP_HOST']
));

echo $tpl->render ('blog/index', $page);

?>
<?php

/**
 * Displays the main blog page.
 */

// Check for a custom handler override
$res = $this->override ('blog/index');
if ($res) { echo $res; return; }

$preview_chars = (int) Appconf::blog('Blog', 'preview_chars') ? (int) Appconf::blog('Blog', 'preview_chars') : false;

$page->id = 'blog';
$page->layout = Appconf::blog ('Blog', 'layout');

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
	if (User::require_acl ('admin', 'blog')) {
		echo '<p class="hide-in-preview"><a href="/blog/add">' . __ ('Add Blog Post') . '</a></p>';
	}
} else {
	if (User::require_acl ('admin', 'blog')) {
		echo '<p class="hide-in-preview"><a href="/blog/add">' . __ ('Add Blog Post') . '</a></p>';
	}

	if (Appconf::blog ('Blog', 'post_format') === 'markdown') {
		require_once ('apps/blog/lib/markdown.php');
	}

	foreach ($posts as $post) {
		$post->url = '/blog/post/' . $post->id . '/' . URLify::filter ($post->title);
		$post->tag_list = (strlen ($post->tags) > 0) ? explode (',', $post->tags) : array ();
		$post->social_buttons = Appconf::blog ('Social Buttons');
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
}

if (! $this->internal) {
	$page->title = Appconf::blog ('Blog', 'title');
}

$page->add_script (sprintf (
	'<link rel="alternate" type="application/rss+xml" href="http://%s/blog/rss" />',
	$_SERVER['HTTP_HOST']
));

echo $tpl->render ('blog/index', $page);

?>

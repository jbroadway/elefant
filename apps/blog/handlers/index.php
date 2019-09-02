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

$footer = Appconf::blog ('Blog', 'post_footer');
$footer_stripped = strip_tags ($footer);
$footer = ($footer && ! empty ($footer_stripped))
	? $tpl->run_includes ($footer)
	: false;

if (! is_array ($posts) || count ($posts) === 0) {
	echo '<p>' . __ ('No posts yet... :(') . '</p>';
	if (User::require_acl ('admin', 'blog', 'admin/add')) {
		echo '<p class="hide-in-preview"><a href="/blog/add">' . __ ('Add Blog Post') . '</a></p>';
	}
} else {
	if (User::require_acl ('admin', 'blog', 'admin/add')) {
		echo '<p class="hide-in-preview"><a href="/blog/add">' . __ ('Add Blog Post') . '</a></p>';
	}

	if (Appconf::blog ('Blog', 'post_format') === 'markdown') {
		require_once ('apps/blog/lib/markdown.php');
	}

	foreach ($posts as $_post) {
		$post = $_post->orig();
		$post->url = '/blog/post/' . $post->id . '/';
		$post->fullurl = $post->url . URLify::filter ($post->title);
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
		} else {
			$post->footer = $footer;
		}
		echo $tpl->render ('blog/post', $post);
	}
}

if (! $this->internal) {
	$page->window_title = Appconf::blog ('Blog', 'title');
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

echo $tpl->render ('blog/index', $page);

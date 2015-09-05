<?php

/**
 * Display the fully browsable latest posts.
 *
 * In PHP code, call it like this:
 *
 *     $this->run ('blog/postsfeed');
 *
 * In a view template, call it like this:
 *
 *     {! blog/postsfeed !}
 *
 * Parameters:
 *
 * - `number` - Number of posts to show (default=5)
 * - `tag` - Show posts with this tag only (optional)
 *
 * Also available in the dynamic objects menu as "Blog: Latest Posts".
 */

require_once ('apps/blog/lib/Filters.php');

$preview_chars = (int) Appconf::blog('Blog', 'preview_chars') ? (int) Appconf::blog('Blog', 'preview_chars') : false;

if ($data['number'] !== '') {
    $limit = $data['number'];
} else {
    $limit = 5;
}

$offset = 0;

$p = new blog\Post;
if (isset ($data['tag']) && $data['tag'] !== '') {
	$posts = $p->tagged ($data['tag'], $limit, $offset);
} else {
	$posts = $p->latest ($limit, $offset);
}
$page->count = $p->query ()->where ('published', 'yes')->count ();

if (Appconf::blog ('Blog', 'post_format') === 'markdown') {
	require_once ('apps/blog/lib/markdown.php');
}

if (! is_array ($posts) || count ($posts) === 0) {
	echo '<p>' . __ ('No posts yet... :(') . '</p>';
	if (User::require_admin ()) {
		echo '<p class="hide-in-preview"><a href="/blog/add">' . __ ('Add Blog Post') . '</a></p>';
	}
} else {
	if (User::require_admin ()) {
		echo '<p class="hide-in-preview"><a href="/blog/add">' . __ ('Add Blog Post') . '</a></p>';
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
				. ' <a href="' . $post->fullurl . '">' . __ ('Read more') . '</a>';
		}
		echo $tpl->render ('blog/post', $post);
	}
}

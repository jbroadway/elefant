<?php

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
}

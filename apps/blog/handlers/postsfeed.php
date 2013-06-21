<?php

require_once ('apps/blog/lib/Filters.php');

$preview_chars = (int) Appconf::blog('Blog', 'preview_chars') ? (int) Appconf::blog('Blog', 'preview_chars') : false;

if ($data['number'] !== '') {
    $page->limit = $data['number'];
} else {
    $page->limit = 5;
}

$page->offset = 0;

$p = new blog\Post;
$posts = $p->latest ($page->limit, $page->offset);
$page->count = $p->query ()->where ('published', 'yes')->count ();

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
		$post->tag_list = explode (',', $post->tags);
		$post->social_buttons = $appconf['Social Buttons'];
		$post->body = $tpl->run_includes ($post->body);
		if ($preview_chars) {
			$post->body = blog_filter_truncate ($post->body, $preview_chars);
		}
		echo $tpl->render ('blog/post', $post);
	}
}

?>
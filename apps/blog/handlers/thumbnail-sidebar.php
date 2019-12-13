<?php

/**
 * Displays the latest blog posts with feature thumbnails for the
 * top three.
 */

require_once ('apps/blog/lib/Filters.php');

$limit = 8;

$posts = blog\Post::query (array ('id', 'ts', 'title', 'thumbnail'))
	->where ('published', 'yes')
	->order ('ts desc')
	->fetch ($limit);

foreach ($posts as $post) {
	if ($post->slug == '') {
		$post->slug = URLify::filter ($post->title);
		$post->put ();
	}
}

$page->add_script ('/apps/blog/css/related.css');

echo $tpl->render ('blog/thumbnail-sidebar', array (
	'posts' => $posts
));

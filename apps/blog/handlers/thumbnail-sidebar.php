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
	->fetch_orig ($limit);

$page->add_script ('/apps/blog/css/related.css');

echo $tpl->render ('blog/thumbnail-sidebar', array (
	'posts' => $posts
));

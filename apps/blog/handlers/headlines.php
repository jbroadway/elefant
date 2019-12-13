<?php

/**
 * Displays the latest blog posts as a bulleted list of links.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('blog/headlines');
 *
 * In a view template, call it like this:
 *
 *     {! blog/headlines !}
 *
 * Parameters:
 *
 * - `limit` - Number of posts to show (default=10)
 * - `tag` - Show posts with this tag only (optional)
 * - `dates` - Show post dates (yes, no, default=no)
 *
 * Also available in the dynamic objects menu as "Blog: Headlines".
 */

if (! $this->internal) {
	$page->id = 'blog';
	$page->layout = $appconf['Blog']['layout'];
	$page->title = __ ('Latest Posts');
}

require_once ('apps/blog/lib/Filters.php');

$limit = isset ($data['limit']) ? $data['limit'] : 10;

$p = new blog\Post;
if (isset ($data['tag']) && $data['tag'] !== '') {
	$posts = $p->tagged ($data['tag']);
} else {
	$posts = $p->headlines ($limit);
}

foreach ($posts as $post) {
	if ($post->slug == '') {
		$post->slug = URLify::filter ($post->title);
	}
}

$dates = (isset ($data['dates']) && $data['dates'] === 'yes') ? true : false;

echo $tpl->render ('blog/headlines', array (
	'posts' => $posts,
	'dates' => $dates
));

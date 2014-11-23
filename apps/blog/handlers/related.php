<?php

/**
 * Show related posts. Called internally by blog/post.
 */

$id = $data['id'];
$tags = explode (',', $data['tags']);

$posts = blog\Post::query ('id, thumbnail, title, ts')
	->where ('published', 'yes')
	->where ('id != ?', $id)
	->and_where (function ($q) use ($tags) {
		foreach ($tags as $n => $tag) {
			$tag = trim ($tag);
			if ($n === 0) {
				$q->where ('tags like ?', '%' . $tag . '%');
			} else {
				$q->or_where ('tags like ?', '%' . $tag . '%');
			}
		}
	})
	->order ('ts', 'desc')
	->fetch_orig (3);

$this->run ('admin/util/minimal-grid');
$page->add_script ('/apps/blog/css/related.css');

echo $tpl->render (
	'blog/related',
	array (
		'posts' => $posts
	)
);

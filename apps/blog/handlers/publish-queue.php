<?php

/**
 * Publish queued blog posts. Use via cron like this:
 *
 *     0,15,30,45 * * * * /var/www/elefant blog/publish-queue
 */

if (! $this->cli) die ('Must be run from the command line.');

$page->layout = false;

// fetch queued posts
$posts = blog\Post::query ()
	->where ('published', 'que')
	->where ('ts <= ?', gmdate ('Y-m-d H:i:s'))
	->fetch ();

// publish posts
foreach ($posts as $post) {
	$post->published = 'yes';
	$post->put ();
	Versions::add ($post);
}

<?php

/**
 * Displays the latest blog posts as a bulleted list of links,
 * with headers grouping posts by month, e.g., "April 2014".
 *
 * Parameters:
 *
 * - `tag`: Show posts by this tag only (optional)
 */

if (! $this->internal) {
	$page->id = 'blog';
	$page->layout = $appconf['Blog']['layout'];
	$page->title = __ ('Latest Posts');
}

require_once ('apps/blog/lib/Filters.php');

$p = new blog\Post;
if (isset ($data['tag']) && $data['tag'] !== '') {
	$posts = $p->tagged ($data['tag']);
} else {
	$posts = $p->headlines ();
}

$bymonth = array ();
foreach ($posts as $post) {
	if ($post->slug == '') {
		$post->slug = URLify::filter ($post->title);
		$post->put ();
	}
	
	$time = strtotime ($post->ts);
	$mmyy = __ (gmdate ('F')) . ' ' . gmdate ('Y');
	if (! is_array ($bymonth[$mmyy])) {
		$bymonth[$mmyy] = array ();
	}
	$bymonth[$mmyy][] = $post;
}

echo $tpl->render ('blog/bymonth', array (
	'posts' => $bymonth
));

$protocol = $this->is_https () ? 'https' : 'http';
$domain = conf ('General', 'site_domain');

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

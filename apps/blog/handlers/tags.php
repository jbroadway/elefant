<?php

/**
 * Renders a tag cloud, with more frequently used tags appearing larger.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('blog/tags');
 *
 * In a view template, call it like this:
 *
 *     {! blog/tags !}
 *
 * Also available in the dynamic objects menu as "Blog: Tag Cloud".
 */

if (! $this->internal) {
	$page->id = 'blog';
	$page->layout = $appconf['Blog']['layout'];
	$page->title = __ ('Tags');
}

$pg = new stdClass;
$pg->tags = blog\Post::tags ();
foreach ($pg->tags as $k => $v) {
	$pg->tags[$k] = ($v / 10 < 2) ? $v / 10 + .9 : (($v / 10 >= 2) ? 3 : $v / 10);
}

echo $tpl->render ('blog/tags', $pg);

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


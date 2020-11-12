<?php

/**
 * Embeds a list of social media buttons without creating separate
 * HTTP requests to each separate platform, making it much faster
 * than embedding each individually.
 *
 * The list of buttons includes:
 *
 * - Facebook
 * - Twitter
 * - Google+
 * - Tumblr
 * - Email
 * - Pinterest
 *
 * For more info, see http://sharingbuttons.io/
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('social/sharingbuttons');
 *
 * In a template, call it like this:
 *
 *     {! social/sharingbuttons !}
 *
 * Parameters:
 *
 * - `title` - An optional title (if not set, uses page title).
 * - `url` - The URL to be shared (if not set, uses current request URI).
 *
 * Also available in the dynamic objects menu as "Social: Sharing Buttons"
 */

$page->add_style ('/apps/social/css/sharingbuttons.css');

if (! isset ($data['title'])) {
	$data['title'] = $page->title;
}

if (! isset ($data['url'])) {
	$data['url'] = $_SERVER['REQUEST_URI'];
}

if (strpos ($data['url'], '/') === 0) {
	$protocol = $this->is_https () ? 'https' : 'http';
	$data['url'] = $protocol . '://' . conf ('General', 'site_domain') . $data['url'];
}

echo $tpl->render ('social/sharingbuttons', $data);

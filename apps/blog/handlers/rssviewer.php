<?php

/**
 * Renders the specified RSS feed `url` as a list of links.
 * Caches the feed for 30 minutes between updates.
 *
 * Parameters:
 *
 * - `url`: The URL of the RSS feed to be displayed.
 */

require_once ('apps/blog/lib/simplepie/autoloader.php');

$feed = new SimplePie ();
$feed->set_feed_url ($data['url']);
$feed->set_cache_duration (1800);
$feed->set_item_limit (10);
$feed->item_limit = 10;
$feed->init ();
$feed->handle_content_type ();

$items = array ();
$list = $feed->get_items (0, 10);
foreach ($list as $item) {
	$items[$item->get_permalink ()] = $item->get_title ();
}

echo $tpl->render ('blog/rssviewer', array ('items' => $items));

?>
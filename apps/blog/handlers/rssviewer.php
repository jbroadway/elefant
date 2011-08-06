<?php

$feed = new SimplePie ($data['url']);
$feed->set_cache_duration (1800);
$feed->set_item_limit (10);
$feed->limit = 10;
$feed->handle_content_type ();

$items = array ();
foreach ($feed->get_items (0, 10) as $item) {
	$items[$item->get_permalink ()] = $item->get_title ();
}

echo $tpl->render ('blog/rssviewer', array ('items' => $items));

?>
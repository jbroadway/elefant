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
	$time = strtotime ($post->ts);
	$mmyy = __ (gmdate ('M')) . ' ' . gmdate ('Y');
	if (! is_array ($bymonth[$mmyy])) {
		$bymonth[$mmyy] = array ();
	}
	$bymonth[$mmyy][] = $post;
}

echo $tpl->render ('blog/bymonth', array (
	'posts' => $bymonth
));

?>
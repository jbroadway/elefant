<?php

/**
 * Displays the latest blog posts as a bulleted list of links.
 */

if (! $this->internal) {
	$page->layout = $appconf['Blog']['layout'];
	$page->title = i18n_get ('Latest Posts');
}

require_once ('apps/blog/lib/Filters.php');

$p = new blog\Post;
if (isset ($data['tag']) && $data['tag'] !== '') {
	$posts = $p->tagged ($data['tag']);
} else {
	$posts = $p->headlines ();
}
$dates = (isset ($data['dates']) && $data['dates'] === 'yes') ? true : false;
echo $tpl->render ('blog/headlines', array (
	'posts' => $posts,
	'dates' => $dates
));

?>
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
$pg->posts = $p->headlines ();
echo $tpl->render ('blog/headlines', $pg);

?>
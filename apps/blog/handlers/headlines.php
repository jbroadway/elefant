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
if ($data['tag'] !== '') {
	$out = array ();
	$tagged = $p->tagged ($data['tag']);
	foreach ($tagged as $post) {
		$out[$post->id] = $post->title;
	}
	$pg->posts = $out;
} else {
	$pg->posts = $p->headlines ();
}
echo $tpl->render ('blog/headlines', $pg);

?>
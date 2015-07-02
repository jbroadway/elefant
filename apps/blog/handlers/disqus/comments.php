<?php

/**
 * Embeds Disqus comments into the current blog post.
 */

$shortname = $appconf['Blog']['disqus_shortname'];

if ($shortname === '') {
	return;
}

echo $tpl->render ('blog/disqus/comments', array (
	'shortname' => $shortname,
	'identifier' => $data['id'],
	'permalink' => $data['fullurl'],
	'title' => $data['title']
));

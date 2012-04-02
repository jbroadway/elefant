<?php

/**
 * Embeds Disqus comments into the current blog post.
 */

echo $tpl->render ('blog/disqus/comments', array (
	'shortname' => $appconf['Blog']['disqus_shortname'],
	'identifier' => $data['id'],
	'permalink' => $data['url'],
	'title' => $data['title']
));

?>
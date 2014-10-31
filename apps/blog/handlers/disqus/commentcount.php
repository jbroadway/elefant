<?php 

/**
 * Display the Disqus comment count for a blog post.
 */

if (self::$called['blog/disqus/commentcount'] === 1) {
	echo $tpl->render ('blog/disqus/commentcount', array (
		'shortname' => $appconf['Blog']['disqus_shortname'],
		'identifier' => $data['id']
	));
}

printf (
	'<a href="%s#disqus_thread" data-disqus-identifier="%s">%s</a>',
	$data['url'],
	$data['id'],
	__ ('comments')
);

<?php 

/**
 * Display the Disqus comment count for a blog post.
 */

$shortname = $appconf['Blog']['disqus_shortname'];

if ($shortname === '') {
	return;
}

if (self::$called['blog/disqus/commentcount'] === 1) {
	echo $tpl->render ('blog/disqus/commentcount', array (
		'shortname' => $shortname,
		'identifier' => $data['id']
	));
}

printf (
	'<a href="%s#disqus_thread" data-disqus-identifier="%s">%s</a>',
	$data['url'],
	$data['id'],
	__ ('comments')
);

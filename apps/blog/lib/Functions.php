<?php

/**
 * Get tags for the blog/headlines handler in the
 * Dynamic Objects dialog.
 */
function blog_get_tags () {
	$out = array ((object) array ('key' => '', 'value' => __ ('- select -')));
	$tags = blog\Post::tags ();
	foreach ($tags as $tag => $count) {
		$out[] = (object) array ('key' => $tag, 'value' => $tag . ' (' . $count . ')');
	}
	return $out;
}

/**
 * Get yes/no for the blog/headlines handler in the
 * Dynamic Objects dialog.
 */
function blog_yes_no () {
	return array (
		(object) array ('key' => 'no', 'value' => __ ('No')),
		(object) array ('key' => 'yes', 'value' => __ ('Yes')),
	);
}

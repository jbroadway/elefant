<?php

function blog_get_tags () {
	$out = array ((object) array ('key' => '', 'value' => i18n_get ('- select -')));
	$tags = blog\Post::tags ();
	foreach ($tags as $tag => $count) {
		$out[] = (object) array ('key' => $tag, 'value' => $tag . ' (' . $count . ')');
	}
	return $out;
}

?>
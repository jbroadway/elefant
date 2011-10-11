<?php

/**
 * Embeds a facebook comment count into the current page. Used by
 * the WYSIWYG editor's dynamic objects menu.
 */

if (! isset ($controller->called['social/facebook/init'])) {
	echo $controller->run ('social/facebook/init');
}

if (strpos ($data['url'], '/') === 0) {
	$data['url'] = 'http://' . $_SERVER['HTTP_HOST'] . $data['url'];
}
echo $tpl->render ('social/facebook/commentcount', $data);

?>
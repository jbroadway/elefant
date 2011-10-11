<?php

/**
 * Embeds a twitter Tweet This button into the current page. Used by
 * the WYSIWYG editor's dynamic objects menu.
 */

if (! isset ($controller->called['social/twitter/init'])) {
	echo $controller->run ('social/twitter/init');
}

if (! isset ($data['via']) || empty ($data['via'])) {
	$data['via'] = $appconf['Twitter']['id'];
}

if (strpos ($data['url'], '/') === 0) {
	$data['url'] = 'http://' . $_SERVER['HTTP_HOST'] . $data['url'];
}
echo $tpl->render ('social/twitter/tweet', $data);

?>
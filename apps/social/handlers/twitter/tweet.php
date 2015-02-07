<?php

/**
 * Embeds a twitter Tweet This button into the current page. Used by
 * the WYSIWYG editor's dynamic objects menu.
 */

if (! isset (self::$called['social/twitter/init'])) {
	echo $this->run ('social/twitter/init');
}

if (! isset ($data['via']) || empty ($data['via'])) {
	$id = Appconf::user ('Twitter', 'twitter_id');
	$data['via'] = (! empty ($id)) ? $id : $appconf['Twitter']['id'];
}

if (strpos ($data['url'], '/') === 0) {
	$data['url'] = '//' . $_SERVER['HTTP_HOST'] . $data['url'];
}
echo $tpl->render ('social/twitter/tweet', $data);

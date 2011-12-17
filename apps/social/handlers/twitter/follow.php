<?php

/**
 * Embeds a twitter Follow button into the current page. Used by
 * the WYSIWYG editor's dynamic objects menu.
 */

if (! isset (self::$called['social/twitter/init'])) {
	echo $this->run ('social/twitter/init');
}

if (! isset ($data['twitter_id'])) {
	$data['twitter_id'] = $appconf['Twitter']['id'];
}

echo $tpl->render ('social/twitter/follow', $data);

?>
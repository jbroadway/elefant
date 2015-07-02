<?php

/**
 * Embeds a facebook like-box into the current page. Used by
 * the WYSIWYG editor's dynamic objects menu.
 */

if (! isset (self::$called['social/facebook/init'])) {
	echo $this->run ('social/facebook/init');
}

if (strpos ($data['url'], '/') === 0) {
	$data['url'] = '//' . $_SERVER['HTTP_HOST'] . $data['url'];
}
echo $tpl->render ('social/facebook/like-box', $data);

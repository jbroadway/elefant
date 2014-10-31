<?php

/**
 * Embeds facebook comments into the current page. Used by
 * the WYSIWYG editor's dynamic objects menu.
 */

if (! isset (self::$called['social/facebook/init'])) {
	echo $this->run ('social/facebook/init');
}

$data['url'] = isset ($data['url'])
	? $data['url']
	: '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

echo $tpl->render ('social/facebook/comments', $data);

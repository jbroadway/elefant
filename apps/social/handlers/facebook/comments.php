<?php

/**
 * Embeds facebook comments into the current page. Used by
 * the WYSIWYG editor's dynamic objects menu.
 */

if (! isset (self::$called['social/facebook/init'])) {
	echo $this->run ('social/facebook/init');
}

echo $tpl->render ('social/facebook/comments', $data);

?>
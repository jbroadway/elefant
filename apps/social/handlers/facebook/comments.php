<?php

/**
 * Embeds facebook comments into the current page. Used by
 * the WYSIWYG editor's dynamic objects menu.
 */

if (! isset ($controller->called['social/facebook/init'])) {
	echo $controller->run ('social/facebook/init');
}

echo $tpl->render ('social/facebook/comments', $data);

?>
<?php

/**
 * Embeds a flash player.
 *
 * Used by the file manager in the WYSIWYG editor when it recognizes an
 * SWF file being embedded.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('filemanager/swf', array ('file' => 'flash-file.swf'));
 *
 * In a template, call it like this:
 *
 *     {! filemanager/swf?file=flash-file.swf !}
 *
 * Parameters:
 *
 * - `file` - The SWF file to play
 *
 * Also available in the dynamic objects menu as "Flash Player (SWF)".
 */

$data['div'] = preg_replace ('/[^a-zA-Z0-9-]+/', '-', trim ($data['file'], '/'));

// rewrite if proxy is set
if ($appconf['General']['proxy_handler']) {
	$data['file'] = str_replace ('/files/', '/filemanager/proxy/', $data['file']);
}

echo $tpl->render ('filemanager/swf', $data);

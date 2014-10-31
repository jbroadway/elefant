<?php

/**
 * Flash file embed handler. Used by the file manager in the WYSIWYG
 * editor when it recognizes an SWF file being embedded.
 */

$data['div'] = preg_replace ('/[^a-zA-Z0-9-]+/', '-', trim ($data['file'], '/'));

// rewrite if proxy is set
if ($appconf['General']['proxy_handler']) {
	$data['file'] = str_replace ('/files/', '/filemanager/proxy/', $data['file']);
}

echo $tpl->render ('filemanager/swf', $data);

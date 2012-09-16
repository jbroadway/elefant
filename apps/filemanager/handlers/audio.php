<?php

/**
 * Audio file embed handler. Used by the file manager in the WYSIWYG
 * editor when it recognizes an MP3 audio file being embedded.
 */

if (! isset (self::$called['filemanager/mediaelement'])) {
	echo $this->run ('filemanager/mediaelement');
}

$data['div'] = preg_replace ('/[^a-zA-Z0-9-]+/', '-', trim ($data['file'], '/'));

// rewrite if proxy is set
if ($appconf['General']['proxy_handler']) {
	$data['file'] = str_replace ('/files/', '/filemanager/proxy/', $data['file']);
}

echo $tpl->render ('filemanager/audio', $data);

?>
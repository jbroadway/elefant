<?php

/**
 * Video file embed handler. Used by the file manager in the WYSIWYG
 * editor when it recognizes an MP4 video file being embedded.
 */

if (! isset ($controller->called['filemanager/mediaelement'])) {
	echo $controller->run ('filemanager/mediaelement');
}

$data['div'] = preg_replace ('/[^a-zA-Z0-9-]+/', '-', trim ($data['file'], '/'));

echo $tpl->render ('filemanager/video', $data);

?>
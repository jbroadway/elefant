<?php

/**
 * Embed the mediaelement audio/video player, used by
 * filemanager/audio and filemanager/video.
 */

if ($controller->called['filemanager/mediaelement'] > 1) {
	return;
}

echo $tpl->render ('filemanager/mediaelement');

?>
<?php

/**
 * Embeds a video player with looping and without player controls. Essentially
 * a more efficient GIF.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('filemanager/videogif', array ('file' => 'my-video.mp4'));
 *
 * In a template, call it like this:
 *
 *     {! filemanager/videogif?file=my-video.mp4 !}
 *
 * Parameters:
 *
 * - `file` - The MP4 file to play
 *
 * Also available in the dynamic objects menu as "Video GIF (MP4)"
 */

// rewrite if proxy is set
if ($appconf['General']['proxy_handler']) {
	$data['file'] = str_replace ('/files/', '/filemanager/proxy/', $data['file']);
	$data['gif'] = str_replace ('/files/', '/filemanager/proxy/', $data['gif']);
}

echo $tpl->render ('filemanager/videogif', $data);

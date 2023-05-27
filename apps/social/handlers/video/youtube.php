<?php

/**
 * Embeds a YouTube video into the current page.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run (
 *         'social/video/youtube',
 *         array ('url' => 'http://youtu.be/xyz')
 *     );
 *
 * In a template, call it like this:
 *
 *     {! social/video/youtube?url=http://youtu.be/xyz !}
 *
 * Parameters:
 *
 * - `url` - The URL of the video page.
 * - `width` - The width of the video player (default = 480)
 * - `height` - The height of the video player (default = 303)
 *
 * Also available in the dynamic objects menu as "Video: YouTube".
 */
 
require_once ('apps/social/lib/Functions.php');

$query = parse_url ($data['url'], PHP_URL_QUERY);
parse_str ($query, $params);
if (isset ($params['v'])) {
	$data['video'] = $params['v'];
} else {
	$data['video'] = substr (parse_url ($data['url'], PHP_URL_PATH), 1);
}

if (isset ($params['t'])) {
	$data['timecode'] = '?start=' . youtube_to_seconds ($params['t']) . '&rel=0';
} else {
	$data['timecode'] = '?rel=0';
}

$data['width'] = isset ($data['width']) ? $data['width'] : 480;
$data['height'] = isset ($data['height']) ? $data['height'] : 303;

echo $tpl->render ('social/video/youtube', $data);

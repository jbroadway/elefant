<?php

/**
 * Embeds a Vimeo video into the current page.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run (
 *         'social/video/vimeo',
 *         array ('url' => 'http://youtu.be/xyz')
 *     );
 *
 * In a template, call it like this:
 *
 *     {! social/video/vimeo?url=http://youtu.be/xyz !}
 *
 * Parameters:
 *
 * - `url` - The URL of the video page.
 * - `width` - The width of the video player (default = 640)
 * - `height` - The height of the video player (default = 360)
 *
 * Also available in the dynamic objects menu as "Video: Vimeo".
 */

$data['video'] = substr (parse_url ($data['url'], PHP_URL_PATH), 1);

$data['width'] = isset ($data['width']) ? $data['width'] : 640;
$data['height'] = isset ($data['height']) ? $data['height'] : 360;

echo $tpl->render ('social/video/vimeo', $data);

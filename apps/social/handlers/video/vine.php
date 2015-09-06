<?php

/**
 * Embeds a Vine.co video into the current page.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run (
 *         'social/video/vine',
 *         array ('url' => 'http://vine.co/xyz')
 *     );
 *
 * In a template, call it like this:
 *
 *     {! social/video/vine?url=http://vine.co/xyz !}
 *
 * Parameters:
 *
 * - `url` - The URL of the video page.
 * - `size` - The width and height of the video player (default = 600)
 * - `audio` - Mute player? (default = yes)
 * - `embed` - Embed type (simple or postcard, default = simple)
 *
 * Also available in the dynamic objects menu as "Video: Vine".
 */
$data['audio'] = ($data['audio'] == 'yes') ? 0 : 1;
$data['size'] = isset ($data['size']) ? $data['size'] : '600';
$data['embed'] = isset ($data['embed']) ? $data['embed'] : 'simple';
$page->add_script("https://platform.vine.co/static/scripts/embed.js");
echo $tpl->render ('social/video/vine', $data);

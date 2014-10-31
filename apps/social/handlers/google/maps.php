<?php

/**
 * Embeds a Google map into the current page. Used by
 * the WYSIWYG editor's dynamic objects menu.
 */

if (self::$called['social/google/maps'] == 1) {
	echo '<script src="//maps.googleapis.com/maps/api/js?sensor=false"></script>';
}

$data['map_id'] = rand ();

$data['width'] = (isset ($data['width']) && ! empty ($data['width'])) ? $data['width'] : '100%';
$data['height'] = (isset ($data['height']) && ! empty ($data['height'])) ? $data['height'] : '400px';
$data['width'] = is_numeric ($data['width']) ? $data['width'] . 'px' : $data['width'];
$data['height'] = is_numeric ($data['height']) ? $data['height'] . 'px' : $data['height'];

echo $tpl->render ('social/google/maps', $data);

<?php

/**
 * Embeds a Google map into the current page. Used by
 * the WYSIWYG editor's dynamic objects menu.
 */

$data['map_id'] = rand ();

$data['width'] = (isset ($data['width']) && ! empty ($data['width'])) ? $data['width'] : '100%';
$data['height'] = (isset ($data['height']) && ! empty ($data['height'])) ? $data['height'] : '400px';
$data['width'] = is_numeric ($data['width']) ? $data['width'] . 'px' : $data['width'];
$data['height'] = is_numeric ($data['height']) ? $data['height'] . 'px' : $data['height'];

$page->add_script ($tpl->render ('social/google/maps_loader', $data));
$page->add_script ($tpl->render ('social/google/maps_script', $data));

echo $tpl->render ('social/google/maps', $data);

<?php

/**
 * Embeds a Google map into the current page. Used by
 * the WYSIWYG editor's dynamic objects menu.
 */

if (self::$called['social/google/maps'] == 1) {
	echo '<script src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>';
}

$data['map_id'] = rand ();
echo $tpl->render ('social/google/maps', $data);

?>
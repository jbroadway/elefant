<?php

/**
 * See social/facebook/pixel helper.
 */

$pixel_id = Appconf::social ('Facebook', 'pixel_id');

if ($pixel_id == null || $pixel_id == false || $pixel_id == '') {
	echo '<!-- Error: Facebook Pixel ID has not been set -->';
	return;
}

if (isset ($data['event'])) {
	echo $tpl->render ('social/facebook/pixel-event', $data);
}

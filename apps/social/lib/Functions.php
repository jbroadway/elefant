<?php
/**
 * Get true/false for any of the social embedd options
 * Dynamic Objects dialog.
 */
function social_true_false () {
	return array (
		(object) array ('key' => 'true', 'value' => __ ('True')),
		(object) array ('key' => 'false', 'value' => __ ('False')),
	);
}

function facebook_light_dark () {
	return array (
		(object) array ('key' => 'light', 'value' => __ ('Light')),
		(object) array ('key' => 'dark', 'value' => __ ('Dark')),
	);
}
?>

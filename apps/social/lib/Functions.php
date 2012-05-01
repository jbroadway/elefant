<?php
/**
 * Get true/false for any of the social embedd options
 * Dynamic Objects dialog.
 */
function social_true_false () {
	return array (
		(object) array ('key' => 'true', 'value' => i18n_get ('True')),
		(object) array ('key' => 'false', 'value' => i18n_get ('False')),
	);
}

function facebook_light_dark () {
	return array (
		(object) array ('key' => 'light', 'value' => i18n_get ('Light')),
		(object) array ('key' => 'dark', 'value' => i18n_get ('Dark')),
	);
}
?>

<?php

/**
 * Get true/false for any of the social embed options
 * Dynamic Objects dialog.
 */
function social_true_false () {
	return array (
		(object) array ('key' => 'true', 'value' => __ ('True')),
		(object) array ('key' => 'false', 'value' => __ ('False')),
	);
}

/**
 * Get yes/no for any of the social embed options
 * Dynamic Objects dialog.
 */
function social_yes_no () {
	return array (
		(object) array ('key' => 'yes', 'value' => __ ('Yes')),
		(object) array ('key' => 'no', 'value' => __ ('No')),
	);
}

/**
 * Get light/dark choice for Facebook embed options.
 */
function facebook_light_dark () {
	return array (
		(object) array ('key' => 'light', 'value' => __ ('Light')),
		(object) array ('key' => 'dark', 'value' => __ ('Dark')),
	);
}

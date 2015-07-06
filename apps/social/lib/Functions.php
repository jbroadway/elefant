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
 * Get size options for vine videos
 * Dynamic Objects dialog.
 */
function vine_size () {
	return array (
		(object) array ('key' => '600', 'value' => '600px'),
		(object) array ('key' => '480', 'value' => '480px'),
		(object) array ('key' => '300', 'value' => '300px')
	);
}

/**
 * Get embed types for vine videos
 * Dynamic Objects dialog.
 */
function vine_embed () {
	return array (
		(object) array('key' => 'simple', 'value' => 'Borderless Layout'),
		(object) array('key' => 'postcard', 'value' => 'Postcard Layout')
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

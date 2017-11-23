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
		(object) array ('key' => 'simple', 'value' => 'Borderless Layout'),
		(object) array ('key' => 'postcard', 'value' => 'Postcard Layout')
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

/**
 * Get Facebook Pixel conversion event options.
 */
function facebook_pixel_events () {
	return array (
		(object) array ('key' => 'Search', 'value' => __ ('Search')),
		(object) array ('key' => 'AddToCart', 'value' => __ ('Add to cart')),
		(object) array ('key' => 'AddToWishlist', 'value' => __ ('Add to wishlist')),
		(object) array ('key' => 'InitiateCheckout', 'value' => __ ('Initiate checkout')),
		(object) array ('key' => 'AddPaymentInfo', 'value' => __ ('Add payment info')),
		(object) array ('key' => 'Purchase', 'value' => __ ('Complete purchase')),
		(object) array ('key' => 'Lead', 'value' => __ ('New lead or submission')),
		(object) array ('key' => 'CompleteRegistration', 'value' => __ ('Complete sign up'))
	);
}

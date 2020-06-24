<?php

/**
 * Get a list of unit options for blocks/group handler in the Dynamic
 * Objects dialog.
 */
function blocks_units () {
	return [
		(object) ['key' => '100', 'value' => 'Multiple rows'],
		(object) ['key' => '50,50', 'value' => '50-50'],
		(object) ['key' => '33,66', 'value' => '33-66'],
		(object) ['key' => '66,33', 'value' => '66-33'],
		(object) ['key' => '25,75', 'value' => '25-75'],
		(object) ['key' => '75,25', 'value' => '75-25'],
		(object) ['key' => '33,33,33', 'value' => '33-33-33'],
		(object) ['key' => '25,50,25', 'value' => '25-50-25'],
		(object) ['key' => '50,25,25', 'value' => '50-25-25'],
		(object) ['key' => '25,25,50', 'value' => '25-25-50'],
		(object) ['key' => '25,25,25,25', 'value' => '25-25-25-25'],
		(object) ['key' => '20,20,20,20,20', 'value' => '20-20-20-20-20']
	];
}

/**
 * Get a list of heading level options for the blocks/group handler in the
 * Dynamic Objects dialog.
 */
function blocks_heading_levels () {
	return [
		(object) ['key' => 'h1', 'value' => __ ('H1')],
		(object) ['key' => 'h2', 'value' => __ ('H2')],
		(object) ['key' => 'h3', 'value' => __ ('H3')],
		(object) ['key' => 'h4', 'value' => __ ('H4')],
		(object) ['key' => 'h5', 'value' => __ ('H5')],
		(object) ['key' => 'h6', 'value' => __ ('H6')]
	];
}

<?php

/**
 * Returns a list of admin tools links to the admin header
 * linking to all the admin handlers for each app that
 * exposes them.
 */

$page->layout = false;
header ('Content-Type: application/json');

if (! User::require_admin ()) {
	return;
}

$custom_tools = admin\Toolbar::custom_tools ($this);
if ($custom_tools === false) {
	$tools = admin\Toolbar::parse_apps ($this);
} else {
	$tools = $custom_tools;
	$custom_tools = true;
}

$out = array (
	'name' => Product::name (),
	'logo' => Product::logo_toolbar (),
	'links' => $tpl->render ('admin/head/links', array (
		'user' => User::val ('name'),
		'tools' => $tools,
		'custom' => $custom_tools
	))
);

echo json_encode ($out);

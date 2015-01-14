<?php

/**
 * Returns a list of admin tools links to the admin header
 * linking to all the admin handlers for each app that
 * exposes them.
 */

$page->layout = false;
header ('Content-Type: application/json');

if (! User::require_admin ()) return;

$tools = admin\Toolbar::tools ($this);
if (count($tools) === 0) {
	$tools = admin\Toolbar::apps($this);
	$is_apps = true;
} else $is_apps = false;

$editable = User::require_acl('admin/toolbar');

$out = array (
	'name' => Product::name (),
	'logo' => Product::logo_toolbar (),
	'is_apps' => $is_apps,
	'editable' => $editable,
	'links' => $tpl->render ('admin/head/links', array (
		'user' => User::val ('name'),
		'tools' => $tools,
		'is_apps' => $is_apps,
		'editable' => $editable
	))
);

echo json_encode ($out);

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
if (count($tools) === 0 && admin\Toolbar::$autofill === false) {
	$tools = admin\Toolbar::apps($this);
	$is_apps = true;
} else {
	if (admin\Toolbar::$autofill) { 
		// Extend the tools list with any unused app resources.
		$apps = admin\Toolbar::apps($this);
		foreach ($tools as $column => $group) {
			// filter out resources that are already in use
			$apps = array_diff_key($apps, $group);
		}
		if (count($apps)) {
			$i = 0;
			$j = 2;
			$column = admin\Toolbar::$autofill;
			$tools[$column] = array();
			foreach ($apps as $handler => $app) {
				if (++$i > 7) {
					$i = 0;
					$column = admin\Toolbar::$autofill .' ('. $j++ .')';
					$tools[$column] = array();
				}
				$tools[$column][$handler] = $apps[$handler];
			}
		}
	}
	$is_apps = false;
}

$editable = User::require_acl('admin/toolbar');
$out = array (
	'name' => Product::name (),
	'logo' => Product::logo_toolbar (),
	'is_apps' => ($is_apps || (count($tools) === 0 && !$editable)),
	'links' => $tpl->render ('admin/head/links', array (
		'user' => User::val ('name'),
		'tools' => $tools,
		'is_apps' => $is_apps,
		'editable' => $editable
	))
);

echo json_encode ($out);

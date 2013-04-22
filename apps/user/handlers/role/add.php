<?php

/**
 * Add a new user role to the site.
 */

$this->require_acl ('admin', 'user', 'user/roles');

$page->title = __ ('Add Role');
$page->layout = 'admin';

$form = new Form ('post', $this);

$resources = User::acl ()->resources ();
unset ($resources['default']);

$form->data = array (
	'_resources' => $resources
);

echo $form->handle (function ($form) use ($page) {
	$_POST['resources'] = isset ($_POST['resources'])
		? $_POST['resources']
		: array ();

	foreach ($_POST['resources'] as $key => $on) {
		$_POST['resources'][$key] = true;
	}

	// convert the resources for saving
	if (! isset ($_POST['resources']['default'])) {
		$_POST['resources']['default'] = false;
	} else {
		$resources = User::acl ()->resources ();
		foreach ($resources as $resource => $label) {
			if (isset ($_POST['resources'][$resource])) {
				unset ($resources[$resource]);
			} else {
				$resources[$resource] = false;
			}
		}
		$resources['default'] = true;
		$_POST['resources'] = $resources;
	}
	info ($_POST);
	
	// save the file
	$acl = User::acl ();
	$acl->add_role ($_POST['name'], $_POST['resources']['default']);
	foreach ($_POST['resources'] as $resource => $allow) {
		if ($allow) {
			$acl->allow ($_POST['name'], $resource);
		} else {
			$acl->deny ($_POST['name'], $resource);
		}
	}
	
	if (! Ini::write ($acl->rules, conf ('Paths', 'access_control_list'))) {
		$form->controller->add_notification (__ ('Unable to save the file.'));
		return false;
	}
	$form->controller->add_notification (__ ('Role added.'));
	$form->controller->redirect ('/user/roles');
});

?>
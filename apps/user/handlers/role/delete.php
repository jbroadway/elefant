<?php

/*
 * Deletes a role.
 */

$this->require_acl ('admin', 'user', 'user/roles');

$page->layout = 'admin';

if (! isset ($_POST['role'])) {
	$this->redirect ('/user/roles');
}

$rules = User::acl ()->rules;
unset ($rules[$_POST['role']]);

if (! Ini::write ($rules, conf ('Paths', 'access_control_list'))) {
	$this->add_notification (__ ('Unable to save the file.'));
} else {
	$this->add_notification (__ ('Role deleted.'));
}
$this->redirect ('/user/roles');

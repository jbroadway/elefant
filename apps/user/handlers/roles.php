<?php

/**
 * List all user roles.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'user', 'user/acl');

$page->title = __ ('Roles');
echo $tpl->render ('user/roles', array (
	'roles' => array_keys (User::acl ()->rules)
));

?>
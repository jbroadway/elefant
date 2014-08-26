<?php

/**
 * Renders the user add form HTML for the user chooser.
 */

$this->require_acl ('admin', 'user');

$u = new User;
$u->type = Appconf::user ('User', 'default_role');
$u->types = User::allowed_roles ();

$page->layout = false;

echo $tpl->render ('user/chooser/addform', $u);

?>
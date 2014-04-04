<?php

/**
 * Renders the user add form HTML for the user chooser.
 */

$this->require_acl ('admin', 'user');

$u = new User;
$u->type = 'admin';
$u->types = array_keys (User::acl ()->rules);

$page->layout = false;

echo $tpl->render ('user/chooser/addform', $u);

?>
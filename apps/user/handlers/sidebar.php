<?php

/**
 * User login/registration sidebar handler.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('user/sidebar');
 *
 * In a template, call it like this:
 *
 *     {! user/sidebar !}
 *
 * Also available in the dynamic objects menu as "User: Sidebar".
 */
$appconf['User']['login_methods'] = (
	isset ($appconf['User']) &&
	isset ($appconf['User']['login_methods']) &&
	is_array ($appconf['User']['login_methods']))
		? $appconf['User']['login_methods']
		: array ();

echo $tpl->render ('user/sidebar', array ('login_methods' => $appconf['User']['login_methods']));

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
$appconf['User']['login_methods'] = is_array ($appconf['User']['login_methods'])
	? $appconf['User']['login_methods']
	: array ();

if (in_array ('persona', $appconf['User']['login_methods'])) {
	header ('X-UA-Compatible: IE=Edge');
	$page->add_script ('https://login.persona.org/include.js');
	$page->add_script (
		sprintf (
			"<script>var persona_redirect=\"%s\", persona_status = %d, persona_active = 0;</script>\n",
			$_SERVER['REQUEST_URI'],
			(int) User::is_valid ()
		)
	);
	$page->add_script ('/apps/user/js/persona.js');
}

echo $tpl->render ('user/sidebar', array ('login_methods' => $appconf['User']['login_methods']));

<?php

$page->template = false;

User::require_login ();
global $user;

$tools = array ();
$res = glob ('apps/*/conf/config.php');
foreach ($res as $file) {
	$appconf = parse_ini_file ($file, true);
	if (isset ($appconf['Admin']['handler'])) {
		$tools[$appconf['Admin']['handler']] = $appconf['Admin']['name'];
	}
}
asort ($tools);

echo $tpl->render ('admin/head/links', array (
	'user' => $user->name,
	'tools' => $tools
));

?>
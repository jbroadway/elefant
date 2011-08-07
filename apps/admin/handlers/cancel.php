<?php

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

// unlock cancelled object
$lock = new Lock ($_GET['type'], $_GET['id']);
$lock->remove ();

// clear autosave
$parts = explode ('/', $_SERVER['HTTP_REFERER']);
array_shift ($parts);
array_shift ($parts);
array_shift ($parts);
$cookie_name = 'autosave-' . preg_replace ('/[^a-z0-9-]/', '', join ('', $parts));
setcookie ($cookie_name, '', time () - 900, '/');

header ('Location: ' . $_GET['return']);
exit;

?>
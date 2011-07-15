<?php

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

$lock = new Lock ($_GET['type'], $_GET['id']);
$lock->remove ();

header ('Location: ' . $_GET['return']);
exit;

?>
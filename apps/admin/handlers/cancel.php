<?php

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

// unlock cancelled object
$lock = new Lock ($_GET['type'], $_GET['id']);
$lock->remove ();

header ('Location: ' . $_GET['return']);
exit;

?>
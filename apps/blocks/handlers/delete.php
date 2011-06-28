<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

$b = new Block ($_GET['id']);

if (! $b->remove ()) {
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $wp->error;
	return;
}

$this->hook ('blocks/delete', $_GET);

if (! isset ($_GET['return'])) {
	$page->title = 'Block Deleted';
	echo '<p>The block has been deleted.</p>';
	echo '<p><a href="/blocks/admin">Continue</a></p>';
} else {
	header ('Location: ' . $_GET['return']);
	exit;
}

?>
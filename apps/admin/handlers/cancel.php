<?php

/**
 * Cancel handler for edit forms. Unlocks the object
 * if there was a lock held on it, then forwards to
 * the specified return location.
 */

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

// unlock cancelled object
$lock = new Lock ($_GET['type'], $_GET['id']);
$lock->remove ();

if (isset ($_GET['return']) {
	$_GET['return'] = filter_var ($_GET['return'], FILTER_SANITIZE_URL);

	if (! validator::validate ($_GET['return'], 'localpath')) {
		$this->redirect ($_GET['return']);
	}
}
$this->redirect ('/');

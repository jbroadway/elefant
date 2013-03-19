<?php

/**
 * Verifies a user's email address based on a verifier key sent to it.
 */

$row = User::query ()
	->where ('email', $_GET['email'])
	->single ();

$data = $row->userdata;

if ($row && isset ($data['verifier']) && $data['verifier'] == $_GET['verifier']) {
	unset ($data['verifier']);
	$row->userdata = $data;
	$row->put ();

	$page->title = __ ('Account Verified');
	echo '<p><a href="/user">' . __ ('Continue') . '</a></p>';
} else {
	$page->title = __ ('Invalid Verifier');
	echo '<p><a href="/">' . __ ('Continue') . '</a></p>';
}

?>
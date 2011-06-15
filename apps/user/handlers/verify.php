<?php

$verified = false;

$res = User::query ()
	->where ('email', $_GET['email'])
	->fetch ();

foreach ($res as $row) {
	$data = $row->userdata;
	if (isset ($data['verifier']) && $data['verifier'] == $_GET['verifier']) {
		unset ($data['verifier']);
		$row->userdata = $data;
		$row->put ();
		$verified = true;
	}
}

if ($verified) {
	$page->title = i18n_get ('Account Verified');
	echo '<p><a href="/user">' . i18n_get ('Continue') . '</a></p>';
} else {
	$page->title = i18n_get ('Invalid Verifier');
	echo '<p><a href="/">' . i18n_get ('Continue') . '</a></p>';
}

?>
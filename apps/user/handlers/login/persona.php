<?php

/**
 * Persona social login handler.
 */

if (! in_array ('persona', $appconf['User']['login_methods'])) {
	echo $this->error (404, __ ('Not found'), __ ('The page you requested could not be found.'));
	return;
}

$page->layout = false;
header ('Content-Type: application/json');

User::init_session ();

$url = 'https://verifier.login.persona.org/verify';
$ch = curl_init ($url);
$data = 'assertion=' . $_POST['assertion'] . '&audience=http://' . $_SERVER['HTTP_HOST'];

curl_setopt_array ($ch, array (
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_POST           => true,
	CURLOPT_POSTFIELDS     => $data,
	CURLOPT_SSL_VERIFYPEER => true,
	CURLOPT_SSL_VERIFYHOST => 2
));

$result = curl_exec ($ch);
curl_close ($ch);

$response = json_decode ($result);
if ($response->status !== 'okay') {
	return $this->restful_error ('Not logged in');
}

$u = User::query ()->where ('email', $response->email)->single ();

if ($u) {
	// already have an account, log them in
	$u->session_id = md5 (uniqid (mt_rand (), 1));
	$u->expires = gmdate ('Y-m-d H:i:s', time () + 2592000);
	$try = 0;
	while (! $u->put ()) {
		$u->session_id = md5 (uniqid (mt_rand (), 1));
		$try++;
		if ($try == 5) {
			echo json_encode (array ('redirect' => $_GET['redirect']));
			return;
		}
	}
	$_SESSION['session_id'] = $u->session_id;

	echo json_encode (array ('redirect' => $_GET['redirect']));
} elseif (isset ($response->email)) {
	// signup form to create a linked account, prefill email
	$_SESSION['persona/email'] = $response->email;
	$_SESSION['persona/redirect'] = $_GET['redirect'];
	$_SESSION['persona/token'] = $response->email;
	echo json_encode (array ('redirect' => '/user/login/newpersona'));
}

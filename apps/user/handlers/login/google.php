<?php

/**
 * Google social login handler.
 */

if (! in_array ('google', $appconf['User']['login_methods'])) {
	echo $this->error (404, __ ('Not found'), __ ('The page you requested could not be found.'));
	return;
}

$session_key = 'google_id_token';
$current_page = '/user/login/google';

User::init_session ();

if (isset ($_GET['redirect'])) {
	$_SESSION['google_login_redirect'] = $_GET['redirect'];
}

$client = new Google\Client ();
$client->setClientId (Appconf::user ('Google', 'oauth_client_id'));
$client->setClientSecret (Appconf::user ('Google', 'oauth_client_secret'));
$client->setRedirectUri ($this->absolutize ($current_page));
$client->setScopes ('email', 'profile');

// If we have a code back from the OAuth 2.0 flow, exchange
// it via fetchAccessTokenWithAuthCode() and store the resulting
// token in the session then redirect to self.
if (isset ($_GET['code'])) {
	$token = $client->fetchAccessTokenWithAuthCode ($_GET['code']);
	$_SESSION[$session_key] = $token;
	$this->redirect ($current_page);
}

// If we have an access token, make the request.
// Otherwise generate an authentication URL.
if (! empty ($_SESSION[$session_key]) && isset ($_SESSION[$session_key]['id_token'])) {
	$client->setAccessToken ($_SESSION[$session_key]);
} else {
	$authUrl = $client->createAuthUrl ();
}

// If we're signed in we can retrieve the ID token.
if ($client->getAccessToken ()) {
	$data = $client->verifyIdToken ();
	$token = 'g:' . $data['sub'];
}

if (isset ($authUrl)) {
	$this->redirect ($authUrl);
}

if (isset ($data['email'])) {
	// fetch by email
	$u = User::query ()->where ('email', $data['email'])->single ();
} else {
	// no email, fetch by token
	$uid = User_OpenID::get_user_id ($token);
	if ($uid) {
		$u = new User ($uid);
	}
}

$redirect = $_SESSION['google_login_redirect'];
unset ($_SESSION['google_login_redirect']);

if ($u) {
	// already have an account, log them in
	$u->session_id = md5 (uniqid (mt_rand (), 1));
	$u->expires = gmdate ('Y-m-d H:i:s', time () + 2592000);
	$try = 0;
	while (! $u->put ()) {
		$u->session_id = md5 (uniqid (mt_rand (), 1));
		$try++;
		if ($try == 5) {
			$this->redirect ($redirect);
		}
	}
	$_SESSION['session_id'] = $u->session_id;

	// save token
	$oid = new User_OpenID (array (
		'token' => $token,
		'user_id' => $u->id
	));
	$oid->put ();

	$this->redirect ($redirect);
} elseif (isset ($data['email'])) {
	// signup form to create a linked account, prefill name and email
	$_POST['name'] = $data['given_name'] . ' ' . $data['family_name'];
	$_POST['email'] = $data['email'];
	$_POST['redirect'] = $redirect;
	$_POST['token'] = $token;
	echo $this->run ('user/login/newuser');
} else {
	// signup form to create a linked account, prefill name
	$_POST['name'] = $data['given_name'] . ' ' . $data['family_name'];
	$_POST['redirect'] = $redirect;
	$_POST['token'] = $token;
	echo $this->run ('user/login/newuser');
}

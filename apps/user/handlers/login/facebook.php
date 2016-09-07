<?php

/**
 * Facebook social login handler.
 */

if (! in_array ('facebook', $appconf['User']['login_methods'])) {
	echo $this->error (404, __ ('Not found'), __ ('The page you requested could not be found.'));
	return;
}

$app_id = $appconf['Facebook']['application_id'];
$app_secret = $appconf['Facebook']['application_secret'];
$my_url = 'http://' . Appconf::admin ('Site Settings', 'site_domain') . '/user/login/facebook?redirect=' . urlencode ($_GET['redirect']);

@session_start ();
$code = $_REQUEST['code'];

if (empty ($code)) {
	$_SESSION['state'] = md5 (uniqid (rand (), true)); //CSRF protection
	$dialog_url = 'http://www.facebook.com/dialog/oauth?client_id=' 
		. $app_id . '&redirect_uri=' . urlencode ($my_url) . '&state='
		. $_SESSION['state'];

	$page->layout = false;
	echo '<script>top.location.href="' . $dialog_url . '";</script>';
	return;
}

if ($_REQUEST['state'] != $_SESSION['state']) {
	$page->title = 'An Error Occurred';
	echo 'Please try again later.';
	return;
}

$token_url = 'https://graph.facebook.com/oauth/access_token?'
	. 'client_id=' . $app_id . '&redirect_uri=' . urlencode ($my_url)
	. '&client_secret=' . $app_secret . '&code=' . $code;

$response = file_get_contents ($token_url);
$params = null;
parse_str ($response, $params);

$graph_url = 'https://graph.facebook.com/me?access_token=' . $params['access_token'];

$user = json_decode (file_get_contents ($graph_url));

$token = 'fb:' . $user->id . ':' . $params['access_token'];
$_SESSION['session_fb'] = $token;

$uid = User_OpenID::get_user_id ($token);
if ($uid) {
	$u = new User ($uid);
}

if ($u) {
	// already have an account, log them in
	$u->session_id = md5 (uniqid (mt_rand (), 1));
	$u->expires = gmdate ('Y-m-d H:i:s', time () + 2592000);
	$try = 0;
	while (! $u->put ()) {
		$u->session_id = md5 (uniqid (mt_rand (), 1));
		$try++;
		if ($try == 5) {
			$this->redirect ($_GET['redirect']);
		}
	}
	$_SESSION['session_id'] = $u->session_id;

	// save openid token
	$oid = new User_OpenID (array (
		'token' => $token,
		'user_id' => $u->id
	));
	$oid->put ();

	$this->redirect ($_GET['redirect']);
} else {

	//Check to see if they already signed in with the same email retreived from facebook
	//If so link that email and Open_ID
	$u = User::query ()
		->where ('email', $user->email)
		->single ();

	if ($u) {
		$oid = new User_OpenID (array (
							'token' => $token,
							'user_id' => $u->id
						));
						
						$oid->put ();

		$u->session_id = md5 (uniqid (mt_rand (), 1));
		$u->expires = gmdate ('Y-m-d H:i:s', time () + 2592000);
		$u->last_login = gmdate('Y-m-d H:i:s');
		$try = 0;
		
		while (! $u->put ()) {
			$u->session_id = md5 (uniqid (mt_rand (), 1));
			$try++;
			if ($try == 5) {
				$this->redirect ($_GET['redirect']);
			}
		}
		
			$_SESSION['session_id'] = $u->session_id;

			// save openid token
			$oid = new User_OpenID (array (
				'token' => $token,
				'user_id' => $u->id
			));
		
		$oid->put ();
		$this->redirect ($_GET['redirect']);
	}

	//Otherwise signup form to create a linked account, prefill name
	$_POST['name'] = $user->name;
	$_POST['redirect'] = $_GET['redirect'];
	$_POST['token'] = $token;
	echo $this->run ('user/login/newuser');
}

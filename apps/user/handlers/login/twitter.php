<?php

$twauth = new tmhOAuth (array (
	'consumer_key' => $appconf['Twitter']['consumer_key'],
	'consumer_secret' => $appconf['Twitter']['consumer_secret']
));

$here = tmhUtilities::php_self ();
if (strpos ($here, '?redirect=') === false) {
	$here .= '?redirect=' . urlencode ($_GET['redirect']);
}
@session_start ();

if (isset ($_SESSION['access_token'])) {
	// already have some credentials stored
	$twauth->config['user_token'] = $_SESSION['access_token']['oauth_token'];
	$twauth->config['user_secret'] = $_SESSION['access_token']['oauth_token_secret'];

	$code = $twauth->request ('GET', $twauth->url ('1/account/verify_credentials'));

	if ($code == 200) {
		$resp = json_decode ($twauth->response['response']);
		// we have $resp->screen_name;
		echo $resp->screen_name;
		return;
	} else {
		// error
		@error_log ('3. ' . $twauth->response['response']);
		$this->redirect ($_GET['redirect']);
	}
} elseif (isset ($_REQUEST['oauth_verifier'])) {
	// we're being called back by Twitter
	$twauth->config['user_token'] = $_SESSION['oauth']['oauth_token'];
	$twauth->config['user_secret'] = $_SESSION['oauth']['oauth_token_secret'];

	$params = array ('oauth_verifier' => $_REQUEST['oauth_verifier']);

	$code = $twauth->request ('POST', $twauth->url ('oauth/access_token', ''), $params);

	if ($code == 200) {
		$_SESSION['access_token'] = $twauth->extract_params ($twauth->response['response']);
		unset ($_SESSION['oauth']);
		$this->redirect ($here);
	} else {
		// error
		@error_log ('2. ' . $twauth->response['response']);
		$this->redirect ($_GET['redirect']);
	}
} else {
	// start oauth dance
	$params = array (
		'oauth_callback' => $here,
		'x_auth_access_type' => 'read'
	);

	$code = $twauth->request ('POST', $twauth->url ('oauth/request_token', ''), $params);

	if ($code == 200) {
		$_SESSION['oauth'] = $twauth->extract_params ($twauth->response['response']);
		$authurl = $twauth->url ('oauth/authenticate', '') . '?oauth_token=' . $_SESSION['oauth']['oauth_token'] . '&force_login=1';
		$this->redirect ($authurl);
	} else {
		// error
		@error_log ('1. ' . $twauth->response['response']);
		$this->redirect ($_GET['redirect']);
	}
}

?>
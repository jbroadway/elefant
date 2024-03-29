<?php

/**
 * This is the settings form for the user app.
 */

$this->require_admin ();

require_once ('apps/admin/lib/Functions.php');

$page->layout = 'admin';
$page->title = __ ('Member Settings');

$form = new Form ('post', $this);

$appconf['User']['login_methods'] = (
	isset ($appconf['User']) &&
	isset ($appconf['User']['login_methods']) &&
	is_array ($appconf['User']['login_methods']))
		? $appconf['User']['login_methods']
		: array ();

$form->data = array (
	'facebook_app_id' => Appconf::user ('Facebook', 'application_id'),
	'facebook_app_secret' => Appconf::user ('Facebook', 'application_secret'),
	'google_oauth_client_id' => Appconf::user ('Google', 'oauth_client_id'),
	'google_oauth_client_secret' => Appconf::user ('Google', 'oauth_client_secret'),
	'twitter_id' => Appconf::user ('Twitter', 'twitter_id'),
	'twitter_key' => Appconf::user ('Twitter', 'consumer_key'),
	'twitter_secret' => Appconf::user ('Twitter', 'consumer_secret'),
	'twitter_access_token' => Appconf::user ('Twitter', 'access_token'),
	'twitter_access_token_secret' => Appconf::user ('Twitter', 'access_token_secret'),
	'login_openid' => in_array ('openid', $appconf['User']['login_methods']),
	'login_google' => in_array ('google', $appconf['User']['login_methods']),
	'login_facebook' => in_array ('facebook', $appconf['User']['login_methods']),
	'login_twitter' => in_array ('twitter', $appconf['User']['login_methods']),
	'login_persona' => in_array ('persona', $appconf['User']['login_methods']),
	'default_role' => Appconf::user ('User', 'default_role'),
	'roles' => array_keys (User::acl ()->rules),
	'_2fa' => $appconf['User']['2fa']
);

echo $form->handle (function ($form) {
	$login_methods = array ();
	if (isset ($_POST['login_openid']) && $_POST['login_openid'] === 'yes') {
		$login_methods[] = 'openid';
	}
	if (isset ($_POST['login_google']) && $_POST['login_google'] === 'yes') {
		$login_methods[] = 'google';
	}
	if (isset ($_POST['login_facebook']) && $_POST['login_facebook'] === 'yes') {
		$login_methods[] = 'facebook';
	}
	if (isset ($_POST['login_twitter']) && $_POST['login_twitter'] === 'yes') {
		$login_methods[] = 'twitter';
	}
	if (isset ($_POST['login_persona']) && $_POST['login_persona'] === 'yes') {
		$login_methods[] = 'persona';
	}
	if (count ($login_methods) === 0) {
		$login_methods = false;
	}

	$merged = Appconf::merge ('user', array (
		'User' => array (
			'login_methods' => $login_methods,
			'default_role' => $_POST['default_role'],
			'2fa' => $_POST['_2fa']
		),
		'Facebook' => array (
			'application_id' => $_POST['facebook_app_id'],
			'application_secret' => $_POST['facebook_app_secret']
		),
		'Twitter' => array (
			'twitter_id' => $_POST['twitter_id'],
			'consumer_key' => $_POST['twitter_key'],
			'consumer_secret' => $_POST['twitter_secret'],
			'access_token' => $_POST['twitter_access_token'],
			'access_token_secret' => $_POST['twitter_access_token_secret']
		),
		'Google' => array (
			'oauth_client_id' => $_POST['google_oauth_client_id'],
			'oauth_client_secret' => $_POST['google_oauth_client_secret']
		)
	));

	if (! Ini::write ($merged, 'conf/app.user.' . ELEFANT_ENV . '.php')) {
		printf ('<p>%s</p>', __ ('Unable to save changes. Check your folder permissions and try again.'));
		return;
	}

	$form->controller->add_notification (__ ('Settings saved.'));
	$form->controller->redirect ('/user/admin');
});

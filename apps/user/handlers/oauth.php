<?php

/**
 * OAuth authorization controller. Authorizes the third party application with the user.
 * 
 * Usage:
 * 
 *     https://example.com/user/oauth?response_type=code&client_id=abc123&state=xyz
 */

// Ensure the user is authorized in the normal way first
$this->require_login ();

$page->id = 'user';
$page->title = __ ('Authorization');

$server = user\Auth\OAuth::init_server ();
$request = OAuth2\Request::createFromGlobals ();
$response = new OAuth2\Response ();

if (! $server->validateAuthorizeRequest ($request, $response)) {
	$response->send ();
	exit;
}

// Create a form to let the user authorize the app
$form = new Form ('post', $this);

echo $form->handle (function ($form) use ($server, $request, $response) {
	$authorized = ($_POST['authorized'] === 'yes');
	$server->handleAuthorizeRequest ($request, $response, $authorized, User::current ()->id);
	$response->send ();
});

<?php

/**
 * OAuth authorization controller. Authorizes the third party application with the user.
 * The third party application will need an entry in the `#prefix#oauth_clients` database
 * table with the associated `client_id`.
 * 
 * Usage:
 * 
 *     https://example.com/user/oauth?response_type=code&scope=basic&client_id=abc123&state=xyz&redirect_uri=https://...
 */

// Ensure the user is authorized in the normal way first
$this->require_login ();

$page->id = 'user';
$page->title = __ ('An application would like to connect to your account');

$server = user\Auth\OAuth::init_server ();
$request = OAuth2\Request::createFromGlobals ();
$response = new OAuth2\Response ();

if (! $server->validateAuthorizeRequest ($request, $response)) {
	$response->send ();
	exit;
}

// Create a form to let the user authorize the app
$form = new Form ('post', $this);

$client_name = Template::sanitize (DB::shift ('select client_name from #prefix#oauth_clients where client_id = ?', $_GET['client_id']));

$form->data = [
	'request_text' => __ ('The app <b>%s</b> is requesting the ability to access your account on your behalf. Allow access?', $client_name)
];

echo $form->handle (function ($form) use ($server, $request, $response) {
	$authorized = ($_POST['authorize'] === 'yes');
	$server->handleAuthorizeRequest ($request, $response, $authorized, User::current ()->id);
	$response->send ();
});

<?php

/**
 * Endpoints to test OAuth connection flow. The test client should redirect
 * to the OAuth authorization controller, fetch an access token, then use it
 * to call an authenticated API endpoint and return its results.
 * 
 * Usage:
 * 
 *     1. Edit the client_id and secret on this class to match an entry in
 *        the #prefix#oauth_clients database table.
 * 
 *     2. Make sure the redirect_uri for the client is set to
 *        https://example.com/user/oauth/token
 * 
 *     3. Log in as a site admin then visit
 *        https://example.com/user/oauth/test/client
 * 
 * You should be forwarded to the OAuth authorization controller and if you
 * approve the request, you should see a response that includes
 * `{"authenticated": true}` on success.
 */

$page->layout = false;

class OAuthRestTest extends \Restful {
	/**
	 * Customize these based on an existing entry in your #prefix#oauth_clients table.
	 */
	private static $state = 'VdxVY1SJvesRad1CJ4GJfkne0mF6nphU';
	private static $client_id = 'abc123';
	private static $secret = 'def456';

	/**
	 * An authenticated API endpoint that returns `{"authenticated": true}` on success.
	 */
	public function get_authenticated () {
		if (! $this->controller->require_auth (\user\Auth\OAuth::init ())) {
			return $this->error ('Unauthorized.');
		}

		return ['authenticated' => true];
	}

	/**
	 * Initiates the OAuth flow by redirect users to the authorization controller.
	 */
	public function get_client () {
		// Require admin to be logged in before these will work
		$this->controller->require_admin ();

		$this->controller->redirect ('/user/oauth?client_id=' . urlencode (self::$client_id) . '&response_type=code&state=' . urlencode (self::$state));
	}

	/**
	 * This handles the redirect from the authorization controller and calls the
	 * token controller followed by the authenticated endpoint above if everything
	 * succeeded.
	 */
	public function get_token () {
		// Require admin to be logged in before these will work
		$this->controller->require_admin ();

		if (! isset ($_GET['code'])) {
			return $this->error ('Missing code.');
		}

		if (! isset ($_GET['state'])) {
			return $this->error ('Missing state.');
		}

		if ($_GET['state'] != self::$state) {
			return $this->error ('Invalid state.');
		}

		// Request access token

		$ch = curl_init ();

		$url = 'http://localhost/user/oauth/token';

		$q = http_build_query ([
			'grant_type' => 'client_credentials',
			'code' => $_GET['code'],
			'client_id' => self::$client_id
		]);

		curl_setopt ($ch, CURLOPT_HTTPHEADER, [
			'Authorization: Basic ' . base64_encode (self::$client_id . ':' . self::$secret)
		]);

		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_POST, true);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $q);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		
		$body = curl_exec ($ch);
		
		if ($body === false) {
			return $this->error ('Error fetching token: ' . curl_error ($ch));
		}

		curl_close ($ch);

		$res = json_decode ($body);
			
		if (json_last_error () !== JSON_ERROR_NONE) {
			return $this->error ('Error parsing token response: ' . $body);
		}

		$access_token = $res->access_token;

		// Make API request

		$ch = curl_init ();

		$url = 'http://localhost/user/oauth/test/authenticated';

		curl_setopt ($ch, CURLOPT_HTTPHEADER, [
			'Authorization: Bearer ' . $access_token
		]);

		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		
		$body = curl_exec ($ch);
		
		if ($body === false) {
			return $this->error ('Error calling endpoint: ' . curl_error ($ch));
		}

		curl_close ($ch);

		$res = json_decode ($body);
			
		if (json_last_error () !== JSON_ERROR_NONE) {
			return $this->error ('Error parsing endpoint response: ' . $body);
		}

		return $res->data;
	}
}

$this->restful (new OAuthRestTest);

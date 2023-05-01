<?php

/**
 * OAuth token controller. Returns the OAuth 2.0 token to the client.
 * 
 * Usage:
 * 
 *     // Note: Requires an associated entry in #prefix#oauth_clients and
 *     // an authorization code received from the /user/oauth authorization
 *     // controller
 * 
 *     curl -u testuser:testpass https://example.com/user/oauth/token -d 'grant_type=client_credentials&code=abc123'
 * 
 * Example response:
 * 
 *     {
 *         "access_token": "03807cb390319329bdf6c777d4dfae9c0d3b3c35",
 *         "expires_in": 3600,
 *         "token_type": "bearer",
 *         "scope": null
 *     }
 */

$server = user\Auth\OAuth::init_server ();
$server->handleTokenRequest (OAuth2\Request::createFromGlobals ())->send ();

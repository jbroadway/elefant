<?php

/**
 * Fetch or generate an API token and secret key for the specified user.
 * Will not regenerate but will create if a token does not exist.
 */

if (! $this->cli) die ("Must be run from the command line.\n");

$page->layout = false;

if (! isset ($_SERVER['argv'][2])) {
	Cli::out ('Usage: ./elefant api/get-token <user-id>', 'info');
	return;
}

$user = new User ($_SERVER['argv'][2]);

if ($user->error) {
	Cli::out ('Error: User not found.', 'error');
	return;
}

$a = api\Api::query ()
	->where ('user_id', $user->id)
	->where ('valid', 'yes')
	->single ();

if ($a && ! $a->error) {
	echo $a->token . ':' . $a->api_key . "\n";
	return;
}

list ($token, $key) = api\Api::create_token ($user->id);
echo $token . ':' . $key . "\n";

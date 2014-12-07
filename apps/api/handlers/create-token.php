<?php

/**
 * Generate or reset an API token and secret key for the specified user.
 * Note that resetting an API token and secret key will cause any use
 * of the old one to fail.
 */

if (! $this->cli) die ("Must be run from the command line.\n");

$page->layout = false;

if (! isset ($_SERVER['argv'][2])) {
	Cli::out ('Usage: ./elefant api/create-token <user-id>', 'info');
	return;
}

$user = new User ($_SERVER['argv'][2]);
if ($user->error) {
	Cli::out ('Error: User not found.', 'error');
	return;
}

list ($token, $key) = api\Api::create_token ($user->id);
echo $token . ':' . $key . "\n";

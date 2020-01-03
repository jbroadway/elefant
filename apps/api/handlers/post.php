<?php

/**
 * This command executes a POST request with HMAC authentication
 * for testing API endpoints.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

$usage = <<<USAGE
Usage:

  <info>./elefant api/post <url> <params> <hmac-token> <hmac-secret></info>

Example:

  <info>./elefant api/post https://www.example.com/api/endpoint \\
    "name=Joe+User&dob=1973-04-21" \\
    1d29ecc97cba75b23d5433fbee5060da \\
    f9761f2dc86a4c9260a9539a64fb3962</info>

USAGE;

if (count ($_SERVER['argv']) < 4) {
	Cli::block ($usage);
	die;
}

$url = $_SERVER['argv'][2];
$params = $_SERVER['argv'][3];
$hmac_token = false;
$hmac_secret = false;

if (count ($_SERVER['argv']) == 6) {
	$hmac_token = $_SERVER['argv'][4];
	$hmac_secret = $_SERVER['argv'][5];
}

$ch = curl_init ();
curl_setopt ($ch, CURLOPT_URL, $url);
curl_setopt ($ch, CURLOPT_POST, true);
curl_setopt ($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt ($ch, CURLOPT_HEADER, true);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);

if ($hmac_token !== false) {
	$hashdata = 'post' . preg_replace ('|^[a-z]+:\/\/|', '', $url) . $params;
	$hash = hash_hmac ('sha256', $hashdata, $hmac_secret);
	curl_setopt ($ch, CURLOPT_USERPWD, $hmac_token . ':' . $hash);
	echo "Sending with token:hash " . $hmac_token . ':' . $hash . PHP_EOL;
}

$res = curl_exec ($ch);

if ($res === false) {
	Cli::out (curl_error ($ch), 'error');
	die;
}

curl_close ($ch);
Cli::block ($res . PHP_EOL);

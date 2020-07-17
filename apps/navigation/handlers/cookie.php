<?php

/**
 * If language negotiation method is set to cookie, use
 * this to set the language cookie. Also accepts an optional
 * redirect parameter to send them to afterwards, otherwise
 * it redirects to the page you just came from.
 *
 * Usage:
 *
 *     /navigation/cookie/fr
 *     /navigation/cookie/fr?redirect=/fr
 */

$lang = count ($this->params) ? $this->params[0] : $i18n->language;

$domain = $domain ? $domain : conf ('General', 'session_domain');

if ($domain === 'full') {
	$domain = Appconf::admin ('Site Settings', 'site_domain');
} elseif ($domain === 'top') {
	$parts = explode ('.', Appconf::admin ('Site Settings', 'site_domain'));
	$tld = array_pop ($parts);
	$domain = '.' . array_pop ($parts) . '.' . $tld;
}

if (version_compare (PHP_VERSION, '7.3.0') >= 0) {
	setcookie (
		$i18n->cookieName,
		$lang,
		[
			'expires' => time () + 2592000, // 1 month
			'path' => '/',
			'domain' => $domain,
			'secure' => false,
			'httponly' => true,
			'samesite' => 'Lax'
		]
	);
} else {
	setcookie (
		$i18n->cookieName,
		$lang,
		time () + 2592000, // 1 month
		'/',
		$domain
	);
}

$_GET['redirect'] = filter_var ($_GET['redirect'], FILTER_SANITIZE_URL);

if (! validator::validate ($_GET['redirect'], 'localpath')) {
	$_GET['redirect'] = '/';
}

isset ($_GET['redirect'])
	? $this->redirect ($_GET['redirect'])
	: $this->redirect ($_SERVER['HTTP_REFERER']);

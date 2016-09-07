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

setcookie (
	$i18n->cookieName,
	$lang,
	time () + 2592000, // 1 month
	'/'
);

$_GET['redirect'] = filter_var ($_GET['redirect'], FILTER_SANITIZE_URL);

if (! validator::validate ($_GET['redirect'], 'localpath')) {
	$_GET['redirect'] = '/';
}

isset ($_GET['redirect'])
	? $this->redirect ($_GET['redirect'])
	: $this->redirect ($_SERVER['HTTP_REFERER']);

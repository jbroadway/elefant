<?php

require_once ('lib/Autoloader.php');

class I18nTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobalsBlacklist = array ('i18n');

	function setUp () {
		global $i18n;
		$_SERVER['REQUEST_URI'] = '/en/pagename';
		$i18n = new I18n ('lang', array ('negotiation_method' => 'url'));
	}

	function test_parameters () {
		global $i18n;

		$this->assertEquals ($i18n->language, 'en');
		$this->assertEquals ($i18n->charset, 'UTF-8');
		$this->assertTrue ($i18n->url_includes_lang);
		$this->assertEquals ($i18n->new_request_uri, '/pagename');
		$this->assertEquals ($i18n->prefix, '/en');
	}

	function test_get () {
		global $i18n;

		$i18n->lang_hash['en'] = array (
			'Hello' => 'Bonjour',
		);
		$this->assertEquals (i18n_get ('Hello'), 'Bonjour');
	}

	function test_getf () {
		global $i18n;

		$i18n->lang_hash['en'] = array (
			'Hello %s' => 'Bonjour %s'
		);
		$this->assertEquals (i18n_getf ('Hello %s', 'world'), 'Bonjour world');
	}

	function test_negotiation_methods () {
		$_SERVER['HTTP_HOST'] = 'en.example.com';
		$i18n = new I18n ('lang', array ('negotiation_method' => 'subdomain'));

		$this->assertEquals ($i18n->language, 'en');

		$_COOKIE['lang'] = 'en';
		$i18n = new I18n ('lang', array ('negotiation_method' => 'cookie'));

		$this->assertEquals ($i18n->language, 'en');

		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.8';
		$i18n = new I18n ('lang', array ('negotiation_method' => 'http'));

		$this->assertEquals ($i18n->language, 'en');
	}

	function test_cascade () {
		global $i18n;

		// Setup fr_ca -> fr fallback
		$i18n = new I18n ();
		$i18n->language = 'fr_ca';
		$i18n->hash_order = array ('fr_ca', 'fr');

		$i18n->lang_hash['fr_ca'] = array (
			'Home' => 'Maison'
		);

		$i18n->lang_hash['fr'] = array (
			'Back' => 'Retournez'
		);

		$this->assertEquals ('Maison', i18n_get ('Home'));
		$this->assertEquals ('Retournez', i18n_get ('Back'));
	}
}

?>
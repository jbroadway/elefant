<?php

require_once ('lib/I18n.php');

class I18nTest extends PHPUnit_Framework_TestCase {
	function test_i81n () {
		global $i18n;
		$_SERVER['REQUEST_URI'] = '/en/pagename';
		$i18n = new I18n ('lang', array ('negotiation_method' => 'url'));

		$this->assertEquals ($i18n->language, 'en');
		$this->assertEquals ($i18n->charset, 'UTF-8');
		$this->assertTrue ($i18n->url_includes_lang);
		$this->assertEquals ($i18n->new_request_uri, '/pagename');
		$this->assertEquals ($i18n->prefix, '/en');

		$i18n->lang_hash['en'] = array (
			'Hello' => 'Bonjour',
			'Hello %s' => 'Bonjour %s'
		);
		$this->assertEquals (i18n_get ('Hello'), 'Bonjour');
		$this->assertEquals (i18n_getf ('Hello %s', 'world'), 'Bonjour world');

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
}

?>
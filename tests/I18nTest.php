<?php

class I18nTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobalsBlacklist = array ('i18n');

	function setUp () {
		global $i18n;
		$_SERVER['REQUEST_URI'] = '/en/pagename';
		$i18n = new I18n ('lang', array ('negotiation_method' => 'url'));
		date_default_timezone_set ('GMT');
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
		$this->assertEquals (__ ('Hello'), 'Bonjour');
	}

	function test_getf () {
		global $i18n;

		$i18n->lang_hash['en'] = array (
			'Hello %s' => 'Bonjour %s'
		);
		$this->assertEquals (__ ('Hello %s', 'world'), 'Bonjour world');
	}

	function test_underscore () {
		global $i18n;

		$i18n->lang_hash['en'] = array (
			'Hello' => 'Bonjour',
		);
		$this->assertEquals (__ ('Hello'), 'Bonjour');

		$i18n->lang_hash['en'] = array (
			'Hello %s' => 'Bonjour %s'
		);
		$this->assertEquals (__ ('Hello %s', 'world'), 'Bonjour world');
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

		// it-IT should still match it
		$i18n->languages = array ('it' => 1, 'en' => 1);
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'it-IT';
		$this->assertEquals ('it', $i18n->negotiate ('http'));

		// should match fr_ca
		$i18n->languages = array ('fr_ca' => 1, 'fr' => 1, 'en' => 1);
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-CA';
		$this->assertEquals ('fr_ca', $i18n->negotiate ('http'));

		// should match fr
		$i18n->languages = array ('fr_ca' => 1, 'fr' => 1, 'en' => 1);
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr';
		$this->assertEquals ('fr', $i18n->negotiate ('http'));
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

		$this->assertEquals ('Maison', __ ('Home'));
		$this->assertEquals ('Retournez', __ ('Back'));
	}

	function test_export () {
		$expected = "<script>\$(function(){\$.i18n_append({\n\t'One': 'One',\n\t'Don\\'t': 'Don\\'t'\n});});</script>\n";
		$this->assertEquals (
			$expected,
			I18n::export (array ('One', 'Don\'t'))
		);

		$this->assertEquals (
			$expected,
			I18n::export ('One', 'Don\'t')
		);
	}

	function test_date () {
		$date = '2012-05-01 18:30:00';
		$time = strtotime ($date);
		$expected = '<time class="date" datetime="2012-05-01T18:30:00+00:00">May 1, 2012</time>';
		$this->assertEquals ($expected, I18n::date ($date));
		$this->assertEquals ($expected, I18n::date ($time));
	}

	function test_short_date () {
		$date = '2012-01-01 18:30:00';
		$time = strtotime ($date);
		$expected = '<time class="shortdate" datetime="2012-01-01T18:30:00+00:00">Jan 1</time>';
		$this->assertEquals ($expected, I18n::short_date ($date));
		$this->assertEquals ($expected, I18n::short_date ($time));
	}

	function test_time () {
		$date = '2012-05-01 18:30:00';
		$time = strtotime ($date);
		$expected = '<time class="time" datetime="2012-05-01T18:30:00+00:00">6:30pm</time>';
		$this->assertEquals ($expected, I18n::time ($date));
		$this->assertEquals ($expected, I18n::time ($time));
	}

	function test_date_time () {
		$date = '2012-05-01 18:30:00';
		$time = strtotime ($date);
		$expected = '<time class="datetime" datetime="2012-05-01T18:30:00+00:00">May 1, 2012 - 6:30pm</time>';
		$this->assertEquals ($expected, I18n::date_time ($date));
		$this->assertEquals ($expected, I18n::date_time ($time));
	}

	function test_short_date_time () {
		$date = '2012-01-01 18:30:00';
		$time = strtotime ($date);
		$expected = '<time class="shortdatetime" datetime="2012-01-01T18:30:00+00:00">Jan 1 - 6:30pm</time>';
		$this->assertEquals ($expected, I18n::short_date_time ($date));
		$this->assertEquals ($expected, I18n::short_date_time ($time));
	}
}

?>
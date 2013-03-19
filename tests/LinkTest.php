<?php

require_once ('lib/Autoloader.php');

class LinkTest extends PHPUnit_Framework_TestCase {
	function setUp () {
		$nav = new Navigation;
		$nav->tree = array (
			(object) array (
				'data' => 'Home',
				'attr' => (object) array ('id' => 'index')
			),
			(object) array (
				'data' => 'Other',
				'attr' => (object) array ('id' => 'other')
			),
			(object) array (
				'data' => 'English',
				'attr' => (object) array ('id' => 'en'),
				'children' => array (
					(object) array (
						'data' => 'About',
						'attr' => (object) array ('id' => 'about'),
						'children' => array (
							(object) array (
								'data' => 'News',
								'attr' => (object) array ('id' => 'news')
							)
						)
					),
					(object) array (
						'data' => 'Contact us',
						'attr' => (object) array ('id' => 'contact-us')
					)
				)
			),
			(object) array (
				'data' => 'Français',
				'attr' => (object) array ('id' => 'fr'),
				'children' => array (
					(object) array (
						'data' => 'À propos',
						'attr' => (object) array ('id' => 'a-propos')
					),
					(object) array (
						'data' => 'Contactez-nous',
						'attr' => (object) array ('id' => 'contactez-nous')
					)
				)
			)
		);
		Link::nav ($nav);
	}

	function test_single_with_http_and_flat () {
		Link::negotiation_method ('http');
		Link::url_style ('flat');
		$i18n = new I18n;
		Link::i18n ($i18n);

		$i18n->prefix = '';
		Link::current ('index');
		$expected = "<li class=\"current\"><a href=\"/index\">Home</a></li>\n";
		$this->assertEquals ($expected, Link::single ('index', 'Home'));

		$i18n->prefix = '/fr';
		$this->assertEquals ($expected, Link::single ('index', 'Home'));

		$expected = "<li><a href=\"/news\">News</a></li>\n";
		$this->assertEquals ($expected, Link::single ('news', 'News'));

		Link::current ('news');
		$expected = "<li class=\"current\"><a href=\"/news\">News</a></li>\n";
		$this->assertEquals ($expected, Link::single ('news', 'News'));

		$expected = "<li class=\"active\"><a href=\"/about\">About</a></li>\n";
		$this->assertEquals ($expected, Link::single ('about', 'About'));
	}

	function test_single_with_http_and_nested () {
		Link::negotiation_method ('http');
		Link::url_style ('nested');
		$i18n = new I18n;
		Link::i18n ($i18n);

		$i18n->prefix = '';
		Link::current ('index');
		$expected = "<li class=\"current\"><a href=\"/index\">Home</a></li>\n";
		$this->assertEquals ($expected, Link::single ('index', 'Home'));

		$i18n->prefix = '/fr';
		$this->assertEquals ($expected, Link::single ('index', 'Home'));

		$expected = "<li><a href=\"/en/about\">About</a></li>\n";
		$this->assertEquals ($expected, Link::single ('about', 'About'));

		Link::current ('about');
		$expected = "<li class=\"current\"><a href=\"/en/about\">About</a></li>\n";
		$this->assertEquals ($expected, Link::single ('about', 'About'));

		$expected = "<li><a href=\"/en/about/news\">News</a></li>\n";
		$this->assertEquals ($expected, Link::single ('news', 'News'));

		Link::current ('news');
		$expected = "<li class=\"current\"><a href=\"/en/about/news\">News</a></li>\n";
		$this->assertEquals ($expected, Link::single ('news', 'News'));

		$expected = "<li class=\"active\"><a href=\"/en/about\">About</a></li>\n";
		$this->assertEquals ($expected, Link::single ('about', 'About'));
	}

	function test_single_with_url_and_flat () {
		Link::negotiation_method ('url');
		Link::url_style ('flat');
		$i18n = new I18n;
		Link::i18n ($i18n);

		$i18n->prefix = '';
		Link::current ('index');
		$expected = "<li class=\"current\"><a href=\"/index\">Home</a></li>\n";
		$this->assertEquals ($expected, Link::single ('index', 'Home'));
		
		$i18n->prefix = '/en';
		$expected = "<li class=\"current\"><a href=\"/en/index\">Home</a></li>\n";
		$this->assertEquals ($expected, Link::single ('index', 'Home'));
		// Note: This would have presumably forwarded to /{$i18n->language} in any case

		$i18n->prefix = '';
		$expected = "<li><a href=\"/en\">English</a></li>\n";
		$this->assertEquals ($expected, Link::single ('en', 'English'));

		$i18n->prefix = '/en';
		$expected = "<li><a href=\"/en\">English</a></li>\n";
		$this->assertEquals ($expected, Link::single ('en', 'English'));

		$i18n->prefix = '';
		$expected = "<li><a href=\"/about\">About</a></li>\n";
		$this->assertEquals ($expected, Link::single ('about', 'About'));

		$i18n->prefix = '/en';
		$expected = "<li><a href=\"/en/about\">About</a></li>\n";
		$this->assertEquals ($expected, Link::single ('about', 'About'));

		$expected = "<li><a href=\"/en/news\">News</a></li>\n";
		$this->assertEquals ($expected, Link::single ('news', 'News'));
	}

	function test_single_with_url_and_nested () {
		Link::negotiation_method ('url');
		Link::url_style ('nested');
		$i18n = new I18n;
		Link::i18n ($i18n);

		$i18n->prefix = '';
		Link::current ('index');
		$expected = "<li class=\"current\"><a href=\"/index\">Home</a></li>\n";
		$this->assertEquals ($expected, Link::single ('index', 'Home'));
		
		$i18n->prefix = '/en';
		$expected = "<li class=\"current\"><a href=\"/index\">Home</a></li>\n";
		$this->assertEquals ($expected, Link::single ('index', 'Home'));
		// Note: This would have presumably forwarded to /{$i18n->language} in any case

		$i18n->prefix = '';
		$expected = "<li><a href=\"/en\">English</a></li>\n";
		$this->assertEquals ($expected, Link::single ('en', 'English'));

		$i18n->prefix = '/en';
		$expected = "<li><a href=\"/en\">English</a></li>\n";
		$this->assertEquals ($expected, Link::single ('en', 'English'));

		$i18n->prefix = '';
		$expected = "<li><a href=\"/en/about\">About</a></li>\n";
		$this->assertEquals ($expected, Link::single ('about', 'About'));

		$i18n->prefix = '/en';
		$expected = "<li><a href=\"/en/about\">About</a></li>\n";
		$this->assertEquals ($expected, Link::single ('about', 'About'));

		$expected = "<li><a href=\"/en/about/news\">News</a></li>\n";
		$this->assertEquals ($expected, Link::single ('news', 'News'));
	}
}

?>
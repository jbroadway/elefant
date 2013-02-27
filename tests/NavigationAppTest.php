<?php

require_once ('lib/Autoloader.php');
require_once ('lib/Functions.php');

class NavigationAppTest extends AppTest {
	static function setUpBeforeClass () {
		parent::setUpBeforeClass ();

		// backup navigation file
		copy ('conf/navigation.json', 'cache/navigation.json');

		// create a test navigation structure
		file_put_contents (
			'conf/navigation.json',
			json_encode (
				array (
					array (
						'data' => 'Home',
						'attr' => array ('id' => 'index')
					),
					array (
						'data' => 'Other',
						'attr' => array ('id' => 'other')
					),
					array (
						'data' => 'English',
						'attr' => array ('id' => 'en'),
						'children' => array (
							array (
								'data' => 'About',
								'attr' => array ('id' => 'about')
							),
							array (
								'data' => 'Contact us',
								'attr' => array ('id' => 'contact-us')
							)
						)
					),
					array (
						'data' => 'Français',
						'attr' => array ('id' => 'fr'),
						'children' => array (
							array (
								'data' => 'À propos',
								'attr' => array ('id' => 'a-propos')
							),
							array (
								'data' => 'Contactez-nous',
								'attr' => array ('id' => 'contactez-nous')
							)
						)
					)
				)
			)
		);
	}

	static function tearDownAfterClass () {
		parent::tearDownAfterClass ();

		// restore backed up navigation file
		copy ('cache/navigation.json', 'conf/navigation.json');
	}

	public function test_top () {
		$page = self::$c->page ();

		$page->id = 'index';
		$res = $this->get ('navigation/top');
		$this->assertContains ('<li class="current"><a href="/index">Home', $res);
		$this->assertContains ('<li><a href="/other">Other', $res);
		
		$page->id = 'other';
		$res = $this->get ('navigation/top');
		$this->assertContains ('<li><a href="/index">Home', $res);
		$this->assertContains ('<li class="current"><a href="/other">Other', $res);
	}

	public function test_section () {
		$page = self::$c->page ();
		
		$page->id = 'about';
		$res = $this->get ('navigation/section', array ('section' => 'en'));
		$this->assertContains ('<li class="current"><a href="/about">About', $res);
		$this->assertContains ('<li><a href="/contact-us">Contact us', $res);
		
		$page->id = 'a-propos';
		$res = $this->get ('navigation/section', array ('section' => 'fr'));
		$this->assertContains ('<li class="current"><a href="/a-propos">À propos', $res);
		$this->assertContains ('<li><a href="/contactez-nous">Contactez-nous', $res);
	}
}

?>
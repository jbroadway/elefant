<?php

class NavigationAppTest extends AppTest {
	static function setUpBeforeClass (): void {
		parent::setUpBeforeClass ();

		// backup navigation file
		copy ('conf/navigation.json', 'cache/navigation.json');

		// create a test navigation structure
		file_put_contents (
			'conf/navigation.json',
			json_encode (
				array (
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
						'children' => (object) array (
							(object) array (
								'data' => 'About',
								'attr' => (object) array ('id' => 'about')
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
				)
			)
		);
	}

	static function tearDownAfterClass (): void {
		parent::tearDownAfterClass ();

		// restore backed up navigation file
		copy ('cache/navigation.json', 'conf/navigation.json');
	}

	public function test_top () {
		Link::reset ();
		Link::current ('index');
		$res = $this->get ('navigation/top');
		$this->assertStringContainsString ('<li class="current"><a href="/">Home', $res);
		$this->assertStringContainsString ('<li><a href="/other">Other', $res);
		
		Link::current ('other');
		$res = $this->get ('navigation/top');
		$this->assertStringContainsString ('<li><a href="/">Home', $res);
		$this->assertStringContainsString ('<li class="current"><a href="/other">Other', $res);
	}

	public function test_section () {
		Link::reset ();
		Link::current ('about');
		$res = $this->get ('navigation/section', array ('section' => 'en'));
		info ($res, true);
		$this->assertStringContainsString ('<li class="current"><a href="/about">About', $res);
		$this->assertStringContainsString ('<li><a href="/contact-us">Contact us', $res);
		
		Link::current ('a-propos');
		$res = $this->get ('navigation/section', array ('section' => 'fr'));
		$this->assertStringContainsString ('<li class="current"><a href="/a-propos">À propos', $res);
		$this->assertStringContainsString ('<li><a href="/contactez-nous">Contactez-nous', $res);
	}
}

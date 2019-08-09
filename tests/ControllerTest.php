<?php

use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase {
	function setUp (): void {
		$this->c = new Controller ();
		$GLOBALS['conf'] = parse_ini_file ('conf/test.php', true);
	}

	function test_route () {
		$this->assertEquals ($this->c->route ('/'), 'apps/admin/handlers/page.php');
		$this->assertEquals ($this->c->route ('/foo'), 'apps/admin/handlers/page.php');
		$this->assertEquals ($this->c->params[0], 'foo');
		$this->assertEquals ($this->c->route ('/admin'), 'apps/admin/handlers/index.php');
		$this->assertEquals ($this->c->route ('/admin/add'), 'apps/admin/handlers/add.php');
		$this->assertEquals ($this->c->route ('/admin/other'), 'apps/admin/handlers/index.php');
		$this->assertEquals ($this->c->params[0], 'other');
		$this->assertEquals ($this->c->route ('/admin/add/one/two/three'), 'apps/admin/handlers/add.php');
		$this->assertEquals ($this->c->params, array ('one', 'two', 'three'));
		$this->assertEquals ($this->c->route ('/foo?bar=asdf'), 'apps/admin/handlers/page.php');
		$this->assertEquals ($this->c->route ('/not/exists'), 'apps/admin/handlers/page.php');
	}

	function test_clean () {
		$this->assertTrue ($this->c->clean ('/foo'));
		$this->assertFalse ($this->c->clean ('/../foo'));
	}
	
	function test_data () {
		$valid = array ('width' => 500, 'email' => 'joe@example.com');
		$invalid = array ('width' => '...', 'email' => 'joe');
		
		$this->c->data ($valid);
		$this->assertEquals ($this->c->data ('width'), 500);
		$this->assertEquals ($this->c->data ('email'), 'joe@example.com');

		$this->assertEquals ($this->c->data ('nonexistant'), null);
		$this->assertEquals ($this->c->data ('nonexistant', 'default'), 'default');

		$this->c->data ($invalid);
		$this->assertEquals ($this->c->data ('width'), '...');
		$this->assertEquals ($this->c->data ('width', 500), '...');
		$this->assertEquals ($this->c->data ('width', 500, array ('type' => 'numeric')), 500);

		$this->assertEquals ($this->c->data ('email'), 'joe');
		$this->assertEquals ($this->c->data ('email', 'joe@example.com'), 'joe');
		$this->assertEquals ($this->c->data ('email', 'joe@example.com', array ('email' => 1)), 'joe@example.com');
	}

	function test_add_param () {
		$this->c->params = array ();
		$this->c->add_param ('two');
		$this->assertEquals ($this->c->add_param ('one'), '.php');
		$this->assertEquals ($this->c->params, array ('one', 'two'));
	}

	function test_internal () {
		$this->assertTrue ($this->c->internal);
	}

	function test_cli () {
		$this->assertTrue ($this->c->cli);
	}

	function test_is_https () {
		$this->assertFalse ($this->c->is_https ());
		$_SERVER['HTTPS'] = 'on';
		$this->assertTrue ($this->c->is_https ());
		$_SERVER['HTTPS'] = 'off';
	}

	function test_absolutize () {
		Appconf::admin ('Site Settings', 'site_domain', 'www.example.com');
		$this->assertEquals ('http://www.example.com/page', $this->c->absolutize ('/page'));
		$this->assertEquals ('http://www.example.com/page', $this->c->absolutize ('//www.example.com/page'));
		$this->assertEquals ('http://www.example.com/page', $this->c->absolutize ('http://www.example.com/page'));
		$this->assertEquals ('http://www.example.com/page', $this->c->absolutize ('page'));
		$this->assertEquals ('http://www.example.com/page', $this->c->absolutize ('page', 'http://www.example.com/'));
		$this->assertEquals ('http://www.example.com/sub/page', $this->c->absolutize ('page', 'http://www.example.com/sub'));
		$this->assertEquals ('http://www.example.com/sub/page', $this->c->absolutize ('page', 'http://www.example.com/sub/'));
	}
}

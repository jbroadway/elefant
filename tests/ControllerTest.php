<?php

require_once ('lib/Functions.php');
require_once ('lib/Autoloader.php');

class ControllerTest extends PHPUnit_Framework_TestCase {
	function setUp () {
		$this->c = new Controller ();
		$GLOBALS['conf'] = parse_ini_file ('conf/config.php', true);
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
	}

	function test_absolutize () {
		$_SERVER['HTTP_HOST'] = 'www.example.com';
		$this->assertEquals ('http://www.example.com/page', $this->c->absolutize ('/page'));
		$this->assertEquals ('http://www.example.com/page', $this->c->absolutize ('//www.example.com/page'));
		$this->assertEquals ('http://www.example.com/page', $this->c->absolutize ('http://www.example.com/page'));
		$this->assertEquals ('http://www.example.com/page', $this->c->absolutize ('page'));
		$this->assertEquals ('http://www.example.com/page', $this->c->absolutize ('page', 'http://www.example.com/'));
		$this->assertEquals ('http://www.example.com/sub/page', $this->c->absolutize ('page', 'http://www.example.com/sub'));
		$this->assertEquals ('http://www.example.com/sub/page', $this->c->absolutize ('page', 'http://www.example.com/sub/'));
	}
}

?>
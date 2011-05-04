<?php

require_once ('lib/Controller.php');

class ControllerTest extends PHPUnit_Framework_TestCase {
	function test_controller () {
		$c = new Controller ();
		$GLOBALS['conf'] = parse_ini_file ('conf/global.php', true);

		$this->assertEquals ($c->route ('/'), 'apps/admin/handlers/page.php');
		$this->assertEquals ($c->route ('/foo'), 'apps/admin/handlers/page.php');
		$this->assertEquals ($c->params[0], 'foo');
		$this->assertEquals ($c->route ('/admin'), 'apps/admin/handlers/index.php');
		$this->assertEquals ($c->route ('/admin/add'), 'apps/admin/handlers/add.php');
		$this->assertEquals ($c->route ('/admin/other'), 'apps/admin/handlers/index.php');
		$this->assertEquals ($c->params[0], 'other');
		$this->assertEquals ($c->route ('/admin/add/one/two/three'), 'apps/admin/handlers/add.php');
		$this->assertEquals ($c->params, array ('one', 'two', 'three'));
		$this->assertEquals ($c->route ('/foo?bar=asdf'), 'apps/admin/handlers/page.php');

		$this->assertTrue ($c->clean ('/foo'));
		$this->assertFalse ($c->clean ('/../foo'));

		$c->params = array ();
		$c->add_param ('two');
		$this->assertEquals ($c->add_param ('one'), '.php');
		$this->assertEquals ($c->params, array ('one', 'two'));
	}
}

?>
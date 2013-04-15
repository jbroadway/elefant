<?php

class AppconfTest extends PHPUnit_Framework_TestCase {
	function test_get () {
		$conf = Appconf::get ('admin');
		$this->assertTrue (is_array ($conf));
		$this->assertTrue (isset ($conf['Admin']));
		$this->assertEquals ('admin/versions', $conf['Admin']['handler']);

		$conf = Appconf::get ('admin', 'Admin');
		$this->assertTrue (is_array ($conf));
		$this->assertEquals ('admin/versions', $conf['handler']);

		$conf = Appconf::get ('admin', 'Admin', 'handler');
		$this->assertEquals ('admin/versions', $conf);
	}

	function test_set () {
		$conf = Appconf::set ('admin', 'Admin', 'handler', 'admin/versions2');
		$this->assertEquals ('admin/versions2', $conf);

		$conf = Appconf::get ('admin', 'Admin', 'handler');
		$this->assertEquals ('admin/versions2', $conf);

		$conf = Appconf::set ('admin', 'Admin', 'handler', 'admin/versions');
		$this->assertEquals ('admin/versions', $conf);

		$conf = Appconf::get ('admin', 'Admin', 'handler');
		$this->assertEquals ('admin/versions', $conf);
	}

	function test_callStatic () {
		$conf = Appconf::admin ();
		$this->assertTrue (is_array ($conf));
		$this->assertTrue (isset ($conf['Admin']));
		$this->assertEquals ('admin/versions', $conf['Admin']['handler']);

		$conf = Appconf::admin ('Admin');
		$this->assertTrue (is_array ($conf));
		$this->assertEquals ('admin/versions', $conf['handler']);

		$conf = Appconf::admin ('Admin', 'handler');
		$this->assertEquals ('admin/versions', $conf);

		$conf = Appconf::admin ('Admin', 'handler', 'admin/versions2');
		$this->assertEquals ('admin/versions2', $conf);

		$conf = Appconf::admin ('Admin', 'handler');
		$this->assertEquals ('admin/versions2', $conf);

		$conf = Appconf::admin ('Admin', 'handler', 'admin/versions');
		$this->assertEquals ('admin/versions', $conf);

		$conf = Appconf::admin ('Admin', 'handler');
		$this->assertEquals ('admin/versions', $conf);
	}
}

?>
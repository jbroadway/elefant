<?php

use PHPUnit\Framework\TestCase;

class EnvconfTest extends TestCase {
	function test_get () {
		putenv ('ADMIN_ADMIN_NAME');
		$conf = Envconf::get ('admin', 'Admin', 'name');
		$this->assertEquals ('Admin', $conf);

		putenv ('ADMIN_ADMIN_NAME=Not admin');
		$conf = Envconf::get ('admin', 'Admin', 'name');
		$this->assertEquals ('Not admin', $conf);
	}

	function test_callStatic () {
		putenv ('ADMIN_ADMIN_NAME');
		$conf = Envconf::admin ('Admin', 'name');
		$this->assertEquals ('Admin', $conf);

		putenv ('ADMIN_ADMIN_NAME=Not admin');
		$conf = Envconf::admin ('Admin', 'name');
		$this->assertEquals ('Not admin', $conf);
	}
}

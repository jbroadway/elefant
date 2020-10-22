<?php

use PHPUnit\Framework\TestCase;

class EnvconfTest extends TestCase {
	function test_get () {
		$_ENV['ADMIN_ADMIN_NAME'] = null;
		$conf = Envconf::get ('admin', 'Admin', 'name');
		$this->assertEquals ('Admin', $conf);
		
		$_ENV['ADMIN_ADMIN_NAME'] = 'Not admin';
		$conf = Envconf::get ('admin', 'Admin', 'name');
		$this->assertEquals ('Not admin', $conf);
	}

	function test_callStatic () {
		$_ENV['ADMIN_ADMIN_NAME'] = null;
		$conf = Envconf::admin ('Admin', 'name');
		$this->assertEquals ('Admin', $conf);

		$_ENV['ADMIN_ADMIN_NAME'] = 'Not admin';
		$conf = Envconf::admin ('Admin', 'name');
		$this->assertEquals ('Not admin', $conf);
	}
}

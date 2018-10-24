<?php

use PHPUnit\Framework\TestCase;

class AppconfTest extends TestCase {
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
	
	function test_options () {
		$payments = Appconf::options ('payments');
		$this->assertEquals (true, is_array ($payments));
		
		$acl = Appconf::options ('acl');
		$this->assertEquals (true, in_array ('filemanager', array_keys ($acl)));
		$this->assertEquals ('Upload and manage files', $acl['filemanager']);
		$this->assertEquals (true, in_array ('user/roles', array_keys ($acl)));
		
		$commands = Appconf::options ('cli', 'commands');
		$expected_commands = array (
			'api/create-token' => 'Generate or reset an API token and secret key for a user.',
			'api/get-token' => 'Fetch or generate an API token and secret key for a user.',
			'blog/publish-queue' => 'Publish scheduled blog posts.'
		);
		foreach ($expected_commands as $command => $name) {
			$this->assertEquals (true, in_array ($command, array_keys ($commands)));
			$this->assertEquals ($name, $commands[$command]);
		}
	}
}

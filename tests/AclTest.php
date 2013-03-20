<?php

class AclTest extends PHPUnit_Framework_TestCase {
	protected static $acl;

	static function setUpBeforeClass () {
		User::$user = (object) array (
			'type' => 'admin'
		);
	}

	static function tearDownAfterClass () {
		User::$user = false;
	}

	function test_init () {
		// Test parsing INI string
		self::$acl = new Acl ('
			[admin]
			default = On
			[member]
			default = Off
			resource = On
		');

		// Should have two roles defined
		$this->assertEquals (2, count (self::$acl->rules));

		// Admin can access anything
		$this->assertTrue ((bool) self::$acl->allowed ('resource'));
	}

	function test_allowed () {
		// Test setting via array
		self::$acl = new Acl (array (
			'admin' => array (
				'default' => true
			),
			'member' => array (
				'default' => false,
				'resource' => true
			)
		));

		// Should have two roles defined
		$this->assertEquals (2, count (self::$acl->rules));

		// Admin can access anything
		$this->assertTrue ((bool) self::$acl->allowed ('resource'));

		// Change to member
		User::$user->type = 'member';

		// Member can access resource
		$this->assertTrue ((bool) self::$acl->allowed ('resource'));

		// Member cannot access anything else
		$this->assertFalse ((bool) self::$acl->allowed ('other'));
	}

	function test_add_role () {
		self::$acl = new Acl (array ());

		// Should have no roles
		$this->assertEquals (0, count (self::$acl->rules));

		// Add a role
		self::$acl->add_role ('member');

		// Should have one role that can access nothing
		$this->assertEquals (1, count (self::$acl->rules));

		// Change to member
		User::$user->type = 'member';

		// Should be able to access nothing
		$this->assertFalse ((bool) self::$acl->allowed ('resource'));

		// Add another role that can access anything
		self::$acl->add_role ('admin', true);

		// Should have two roles
		$this->assertEquals (2, count (self::$acl->rules));

		// Change to admin
		User::$user->type = 'admin';

		// Should be able to access anything
		$this->assertTrue ((bool) self::$acl->allowed ('resource'));
	}

	function test_deny () {
		self::$acl = new Acl (array ());

		// Should have no roles
		$this->assertEquals (0, count (self::$acl->rules));

		// Add an admin role that can access anything
		self::$acl->add_role ('admin', true);

		// Change to admin
		User::$user->type = 'admin';

		// Deny them access to a resource
		self::$acl->deny ('admin', 'resource');

		// Should be able to access anything except 'resource'
		$this->assertFalse ((bool) self::$acl->allowed ('resource'));
		$this->assertTrue ((bool) self::$acl->allowed ('other'));
	}

	function test_allow () {
		self::$acl = new Acl (array ());

		// Should have no roles
		$this->assertEquals (0, count (self::$acl->rules));

		// Add an admin role that can access nothing
		self::$acl->add_role ('member', false);

		// Change to admin
		User::$user->type = 'member';

		// Allow them access to a resource
		self::$acl->allow ('member', 'resource');

		// Should be able to access only 'resource'
		$this->assertTrue ((bool) self::$acl->allowed ('resource'));
		$this->assertFalse ((bool) self::$acl->allowed ('other'));
	}
}

?>
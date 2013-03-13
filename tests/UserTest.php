<?php

require_once ('lib/Autoloader.php');

class UserTest extends PHPUnit_Framework_TestCase {
	static function setUpBeforeClass () {
		DB::open (array ('master' => true, 'driver' => 'sqlite', 'file' => ':memory:'));
		DB::execute ('create table #prefix#user (
			id integer primary key,
			email char(72) unique not null,
			password char(128) not null,
			session_id char(32) unique,
			expires datetime not null,
			name char(72) not null,
			type char(32) not null,
			signed_up datetime not null,
			updated datetime not null,
			userdata text not null
		)');
	}

	function test_encrypt_pass () {
		$encrypted = User::encrypt_pass ('testing');
		$this->assertEquals ($encrypted, crypt ('testing', $encrypted));
		$this->assertTrue (($encrypted === crypt ('testing', $encrypted)));
		$this->assertFalse (($encrypted === crypt ('other', $encrypted)));
	}

	function test_add () {
		$u = new User (array (
			'id' => 1,
			'email' => 'you@example.com',
			'password' => User::encrypt_pass ('testing'),
			'expires' => gmdate ('Y-m-d H:i:s'),
			'name' => 'Test User',
			'type' => 'member',
			'signed_up' => gmdate ('Y-m-d H:i:s'),
			'updated' => gmdate ('Y-m-d H:i:s'),
			'userdata' => json_encode (array ())
		));
		$this->assertTrue ($u->put ());
		$this->assertEquals (1, $u->id);
		$this->assertEquals ('member', $u->type);
	}

	function test_get () {
		$u = new User (1);
		$this->assertEquals ('Test User', $u->name);
	}

	function test_val () {
		User::$user = new User (1);
		$this->assertEquals ('Test User', User::val ('name'));
		$this->assertEquals ('Test User 2', User::val ('name', 'Test User 2'));
		$this->assertEquals ('Test User 2', User::val ('name'));
	}

	function test_is () {
		$this->assertTrue (User::is ('member'));
		$this->assertFalse (User::is ('admin'));
	}

	function test_userdata () {
		$data = User::val ('userdata');

		$this->assertEquals (array (), $data);
		$data['foo'] = 'bar';
		User::val ('userdata', $data);
		$this->assertEquals (
			json_encode (array ('foo' => 'bar')),
			User::$user->data['userdata']
		);
	}

	function test_current () {
		$user = User::$user;
		$u2 = User::current ();
		$this->assertEquals ($user, $u2);
		User::current ($u2);
		$u3 = User::current ();
		$this->assertEquals ($user, $u3);
	}
}

?>
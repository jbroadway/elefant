<?php

require_once ('lib/Database.php');

class DatabaseTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobalsBlacklist = array ('db_list', 'db_err', 'db_sql', 'db_args');

	static function setUpBeforeClass () {
		$GLOBALS['db_list'] = array ();
	}

	function setUp () {
		$this->bad_conf = array (
			'master' => true,
			'driver' => 'fake_driver',
			'host' => '127.0.0.1',
			'name' => 'fake_db',
			'user' => 'fake',
			'pass' => 'fake'
		);
		$this->conf = array (
			'master' => true,
			'driver' => 'sqlite',
			'file' => ':memory:'
		);
	}

	static function tearDownAfterClass () {
		unset ($GLOBALS['db_list']);
	}

	function test_open () {
		$this->assertFalse (db_open ($this->bad_conf));
		$this->assertEquals ('could not find driver', db_error ());
		$this->assertTrue (db_open ($this->conf));
	}

	function test_args () {
		$cmp = array ('one', 'two');
		$obj = (object) $cmp;
		$this->assertEquals (db_args (array ('one', 'two')), $cmp);
		$this->assertEquals (db_args (array (array ('one', 'two'))), $cmp);
		$this->assertEquals (db_args (array ($obj)), $cmp);
	}

	function test_execute () {
		$this->assertTrue (db_execute ('create table test ( foo int )'));
		$this->assertTrue (db_execute ('insert into test (foo) values (?)', 'asdf'));
	}

	function test_last_sql () {
		$this->assertEquals (db_last_sql (), 'insert into test (foo) values (?)');
	}

	function test_last_args () {
		$this->assertEquals (db_last_args (), array ('asdf'));
	}

	function test_fetch_array () {
		$res = db_fetch_array ('select * from test');
		$this->assertEquals (count ($res), 1);
		$cmp = new StdClass;
		$cmp->foo = 'asdf';
		$this->assertEquals ($res[0], $cmp);
	}

	function test_single () {
		$cmp = new StdClass;
		$cmp->foo = 'asdf';
		$res = db_single ('select * from test');
		$this->assertEquals ($res, $cmp);
	}

	function test_shift () {
		$res = db_shift ('select foo from test');
		$this->assertEquals ($res, 'asdf');
	}

	function test_shift_array () {		
		$res = db_shift_array ('select * from test');
		$this->assertEquals ($res, array ('asdf'));
	}

	function test_pairs () {
		$cmp = new StdClass;
		$cmp->foo = 'asdf';
		db_execute ('create table test2 (foo char(12), bar char(12))');
		db_execute ('insert into test2 (foo, bar) values (?, ?)', 'one', 'joe');
		db_execute ('insert into test2 (foo, bar) values (?, ?)', 'two', 'sam');
		$res = db_pairs ('select * from test2 order by foo asc');
		$cmp = array ('one' => 'joe', 'two' => 'sam');
		$this->assertEquals ($res, $cmp);
	}
}

?>
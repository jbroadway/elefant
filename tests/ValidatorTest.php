<?php

require_once ('lib/Autoloader.php');

class ValidatorTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobalsBlacklist = array ('user');

	static function setUpBeforeClass () {
		DB::open (array ('master' => true, 'driver' => 'sqlite', 'file' => ':memory:'));
	}

	function test_validate () {
		$this->assertTrue (Validator::validate ('1234', 'regex', '/^[0-9]+$/'));
		$this->assertFalse (Validator::validate ('adsf', 'regex', '/^[0-9]+$/'));
		$this->assertTrue (Validator::validate ('123', 'type', 'numeric'));
		$this->assertFalse (Validator::validate ('asdf', 'type', 'numeric'));
		$this->assertTrue (Validator::validate ('123', 'callback', function ($value) { return true; }));
		$this->assertFalse (Validator::validate ('123', 'callback', function ($value) { return false; }));
		$this->assertFalse (Validator::validate ('asdf', 'length', 2));
		$this->assertFalse (Validator::validate ('asdf', 'length', '5+'));
		$this->assertTrue (Validator::validate ('asdf', 'length', '5-'));
		$this->assertFalse (Validator::validate ('asdf', 'length', '6-8'));
		$this->assertTrue (Validator::validate ('asdf', 'length', '2-6'));
		$this->assertTrue (Validator::validate (5, 'range', '1-10'));
		$this->assertFalse (Validator::validate (15, 'range', '1-10'));
		$this->assertTrue (Validator::validate ('', 'empty'));
		$this->assertFalse (Validator::validate ('asdf', 'empty'));
		$this->assertTrue (Validator::validate ('foo@bar.com', 'email'));
		$this->assertFalse (Validator::validate ('@foo@bar.com', 'email'));
		$this->assertFalse (Validator::validate ('foo@bar', 'email'));
		$this->assertTrue (Validator::validate ('foo+spam@foo.bar.org', 'email'));
		$this->assertTrue (Validator::validate ("asdf", 'header'));
		$this->assertFalse (Validator::validate ("asdf\nasdf", 'header'));
		$this->assertTrue (Validator::validate ('2010-01-01', 'date'));
		$this->assertFalse (Validator::validate ('2010-01-010', 'date'));
		$this->assertTrue (Validator::validate ('2010-01-01 00:01:01', 'datetime'));
		$this->assertFalse (Validator::validate ('2010-01-01-00:01:01', 'datetime'));
		$this->assertTrue (Validator::validate ('00:01:01', 'time'));
		$this->assertFalse (Validator::validate ('000101', 'time'));
		$this->assertTrue (Validator::validate ('Template.php', 'exists', 'lib'));
		$this->assertFalse (Validator::validate ('ASDF.php', 'exists', 'lib'));
		$this->assertTrue (Validator::validate ('default', 'exists', 'layouts/%s.html'));
		$this->assertTrue (Validator::validate ('foobar', 'contains', 'foo'));
		$this->assertFalse (Validator::validate ('foobar', 'contains', 'asdf'));
		$this->assertTrue (Validator::validate ('asdf', 'equals', 'asdf'));
		$this->assertFalse (Validator::validate ('foobar', 'equals', 'asdf'));
		$this->assertTrue (Validator::validate ('asdf', 'unique', 'user.email'));
		DB::execute ('create table test ( email char(48) )');
		DB::execute ('insert into test (email) values (?)', 'foo.bar@gmail.com');
		$this->assertTrue (Validator::validate ('bar.foo@gmail.com', 'unique', 'test.email'));
		$this->assertFalse (Validator::validate ('foo.bar@gmail.com', 'unique', 'test.email'));
		$this->assertTrue (Validator::validate (5, 'lt', 10));
		$this->assertFalse (Validator::validate (50, 'lt', 10));
		$this->assertTrue (Validator::validate (10, 'lte', 10));
		$this->assertFalse (Validator::validate (50, 'lte', 10));
		$this->assertTrue (Validator::validate (50, 'gt', 10));
		$this->assertFalse (Validator::validate (5, 'gt', 10));
		$this->assertTrue (Validator::validate (10, 'gte', 10));
		$this->assertFalse (Validator::validate (5, 'gte', 10));
		$_POST['test'] = 'foo';
		$this->assertTrue (Validator::validate ('foo', 'matches', '$_POST["test"]'));
		$this->assertFalse (Validator::validate ('bar', 'matches', '$_POST["test"]'));
		$this->assertFalse (Validator::validate ('foo', 'not matches', '$_POST["test"]'));
		$this->assertTrue (Validator::validate ('bar', 'not matches', '$_POST["test"]'));
		$this->assertTrue (Validator::validate ('http://foo.com/bar', 'url'));
		$this->assertFalse (Validator::validate ('foobar', 'url'));
		$this->assertFalse (Validator::validate ('http:/fooobar', 'url'));

		// test array validation
		$valid_emails = array (
			'joe@example.com',
			'sue@example.com'
		);
		$invalid_emails = array (
			'joe.example dot com',
			'sue@localhost'
		);
		$this->assertTrue (Validator::validate ($valid_emails, 'each email', 1));
		$this->assertFalse (Validator::validate ($invalid_emails, 'each email', 1));
		$names = array ('Joe', 'Sue');
		$empty = array ('', '');
		$this->assertTrue (Validator::validate ($names, 'each not empty', 1));
		$this->assertFalse (Validator::validate ($empty, 'each not empty', 1));
	}

	function test_validate_list () {
		$values = array (
			'foo' => 'bar',
			'asdf' => 'qwerty'
		);
		$validations = array (
			'foo' => array (
				'not empty' => 1
			),
			'asdf' => array (
				'empty' => 1
			)
		);
		$this->assertEquals (array ('asdf'), Validator::validate_list ($values, $validations));

		$validations = array (
			'foo' => array (
				'skip_if_empty' => 1,
				'contains' => 'asdf'
			)
		);
		$values = array (
			'foo' => '',
			'asdf' => 'qwerty'
		);
		$this->assertEquals (array (), Validator::validate_list ($values, $validations));
		$values['foo'] = 'foobar';
		$this->assertEquals (array ('foo'), Validator::validate_list ($values, $validations));
		$values['foo'] = 'asdf';
		$this->assertEquals (array (), Validator::validate_list ($values, $validations));

		$validations = array (
			'foo' => array (
				'type' => 'array',
				'skip_if_empty' => 1,
				'each contains' => 'asdf'
			)
		);
		$values = array ('foo' => 'asdf'); // Not an array
		$this->assertEquals (array ('foo'), Validator::validate_list ($values, $validations));
		$values = array ('foo' => array ('bar', '')); // Contains should fail
		$this->assertEquals (array ('foo'), Validator::validate_list ($values, $validations));
		$values = array ('foo' => array ('', '')); // All empty should pass
		$this->assertEquals (array (), Validator::validate_list ($values, $validations));
	}
}

?>
<?php

require_once ('lib/Form.php');
require_once ('lib/Database.php');

class FormTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobalsBlacklist = array ('db_list', 'db_err', 'db_sql', 'db_args', 'user');

	static function setUpBeforeClass () {
		db_open (array ('master' => true, 'driver' => 'sqlite', 'file' => ':memory:'));
	}

	static function tearDownAfterClass () {
		unset ($GLOBALS['db_list']);
	}

	function test_verify_value () {
		$this->assertTrue (Form::verify_value ('1234', 'regex', '/^[0-9]+$/'));
		$this->assertFalse (Form::verify_value ('adsf', 'regex', '/^[0-9]+$/'));
		$this->assertTrue (Form::verify_value ('123', 'type', 'numeric'));
		$this->assertFalse (Form::verify_value ('asdf', 'type', 'numeric'));
		$this->assertTrue (Form::verify_value ('123', 'callback', function ($value) { return true; }));
		$this->assertFalse (Form::verify_value ('123', 'callback', function ($value) { return false; }));
		$this->assertFalse (Form::verify_value ('asdf', 'length', 2));
		$this->assertFalse (Form::verify_value ('asdf', 'length', '5+'));
		$this->assertTrue (Form::verify_value ('asdf', 'length', '5-'));
		$this->assertFalse (Form::verify_value ('asdf', 'length', '6-8'));
		$this->assertTrue (Form::verify_value ('asdf', 'length', '2-6'));
		$this->assertTrue (Form::verify_value (5, 'range', '1-10'));
		$this->assertFalse (Form::verify_value (15, 'range', '1-10'));
		$this->assertTrue (Form::verify_value ('', 'empty'));
		$this->assertFalse (Form::verify_value ('asdf', 'empty'));
		$this->assertTrue (Form::verify_value ('foo@bar.com', 'email'));
		$this->assertFalse (Form::verify_value ('@foo@bar.com', 'email'));
		$this->assertTrue (Form::verify_value ("asdf", 'header'));
		$this->assertFalse (Form::verify_value ("asdf\nasdf", 'header'));
		$this->assertTrue (Form::verify_value ('2010-01-01', 'date'));
		$this->assertFalse (Form::verify_value ('2010-01-010', 'date'));
		$this->assertTrue (Form::verify_value ('2010-01-01 00:01:01', 'datetime'));
		$this->assertFalse (Form::verify_value ('2010-01-01-00:01:01', 'datetime'));
		$this->assertTrue (Form::verify_value ('00:01:01', 'time'));
		$this->assertFalse (Form::verify_value ('000101', 'time'));
		$this->assertTrue (Form::verify_value ('Template.php', 'exists', 'lib'));
		$this->assertFalse (Form::verify_value ('ASDF.php', 'exists', 'lib'));
		$this->assertTrue (Form::verify_value ('default', 'exists', 'layouts/%s.html'));
		$this->assertTrue (Form::verify_value ('foobar', 'contains', 'foo'));
		$this->assertFalse (Form::verify_value ('foobar', 'contains', 'asdf'));
		$this->assertTrue (Form::verify_value ('asdf', 'equals', 'asdf'));
		$this->assertFalse (Form::verify_value ('foobar', 'equals', 'asdf'));
		$this->assertTrue (Form::verify_value ('asdf', 'unique', 'user.email'));
		db_execute ('create table test ( email char(48) )');
		db_execute ('insert into test (email) values (?)', 'foo.bar@gmail.com');
		$this->assertTrue (Form::verify_value ('bar.foo@gmail.com', 'unique', 'test.email'));
		$this->assertFalse (Form::verify_value ('foo.bar@gmail.com', 'unique', 'test.email'));
		$this->assertTrue (Form::verify_value (5, 'lt', 10));
		$this->assertFalse (Form::verify_value (50, 'lt', 10));
		$this->assertTrue (Form::verify_value (10, 'lte', 10));
		$this->assertFalse (Form::verify_value (50, 'lte', 10));
		$this->assertTrue (Form::verify_value (50, 'gt', 10));
		$this->assertFalse (Form::verify_value (5, 'gt', 10));
		$this->assertTrue (Form::verify_value (10, 'gte', 10));
		$this->assertFalse (Form::verify_value (5, 'gte', 10));
		$_POST['test'] = 'foo';
		$this->assertTrue (Form::verify_value ('foo', 'matches', '$_POST["test"]'));
		$this->assertFalse (Form::verify_value ('bar', 'matches', '$_POST["test"]'));
		$this->assertFalse (Form::verify_value ('foo', 'not matches', '$_POST["test"]'));
		$this->assertTrue (Form::verify_value ('bar', 'not matches', '$_POST["test"]'));
	}

	function test_verify_values () {
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
		$this->assertEquals (array ('asdf'), Form::verify_values ($values, $validations));

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
		$this->assertEquals (array (), Form::verify_values ($values, $validations));
		$values['foo'] = 'foobar';
		$this->assertEquals (array ('foo'), Form::verify_values ($values, $validations));
		$values['foo'] = 'asdf';
		$this->assertEquals (array (), Form::verify_values ($values, $validations));
	}

	function test_merge_values () {
		$_POST['foo'] = 'bar';
		$obj = new StdClass;
		$obj->foo = 'asdf';
		$form = new Form ('post');
		$obj = $form->merge_values ($obj);
		$this->assertEquals ($obj->foo, $_POST['foo']);
	}

	function test_submit () {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['HTTP_HOST'] = 'www.test.com';
		$_SERVER['HTTP_REFERER'] = 'http://www.test.com/app/foo';
		$form = new Form ('post');
		$this->assertEquals ($form->method, 'post');
		$this->assertEquals ($form->rules, array ());

		$this->assertFalse ($form->submit ());
		$this->assertEquals ($form->error, 'Cross-site request forgery detected.');

		$form->verify_csrf = false;
		$this->assertTrue ($form->submit ());

		$_SERVER['HTTP_REFERER'] = 'http://www.other.com/foo.bar';
		$this->assertFalse ($form->submit ());
		$this->assertEquals ($form->error, 'Referrer must match the host name.');
	}

	function test_verify_request_method () {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$form = new Form ($_SERVER['REQUEST_METHOD']);
		$this->assertTrue ($form->verify_request_method ());
	}

	function test_verify_referrer () {
		$form = new Form ();
		$_SERVER['HTTP_HOST'] = 'www.test.com';
		$_SERVER['HTTP_REFERER'] = 'http://www.test.com/app/foo';
		$this->assertTrue ($form->verify_referrer ());
		$_SERVER['HTTP_REFERER'] = 'http://www.other.com/foo.bar';
		$this->assertFalse ($form->verify_referrer ());
	}

	function test_initialize_csrf () {
		$form = new Form ();
		$form->initialize_csrf ();
		$this->assertRegExp ('/^[a-zA-Z0-9]+$/', $form->csrf_token);
		$this->assertEquals ($_SESSION['csrf_token'], $form->csrf_token);
		$this->assertGreaterThan (time (), $_SESSION['csrf_expires']);

		$token = $form->csrf_token;
		$form->initialize_csrf ();
		$this->assertEquals ($token, $form->csrf_token);
		$this->assertEquals ($_SESSION['csrf_token'], $form->csrf_token);
		$this->assertGreaterThan (time (), $_SESSION['csrf_expires']);
	}

	function test_generate_csrf_script () {
		$form = new Form ();
		$form->csrf_field_name = 'TOKEN';
		$form->initialize_csrf ();
		$token = $form->csrf_token;

		$res = $form->generate_csrf_script ();
		$this->assertEquals (
			'<script>$(function(){$("form").append("<input type=\'hidden\' name=\'TOKEN\' value=\'' . $token . '\'/>");});</script>',
			$res
		);
	}

	function test_verify_csrf () {
		$form = new Form ();
		$form->initialize_csrf ();

		$this->assertFalse ($form->verify_csrf ());

		$_POST['_token_'] = $form->csrf_token;
		$this->assertTrue ($form->verify_csrf ());

		$_SESSION['csrf_expires'] = time () - 10;
		$this->assertFalse ($form->verify_csrf ());
	}
}

?>
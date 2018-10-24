<?php

use PHPUnit\Framework\TestCase;

class FormTest extends TestCase {
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
		Appconf::admin ('Site Settings', 'site_domain', 'www.test.com');
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

		$token = $form->csrf_token;
		$form->initialize_csrf ();
		$this->assertEquals ($token, $form->csrf_token);
	}

	function test_generate_csrf_script () {
		$form = new Form ();
		$form->csrf_field_name = 'TOKEN';
		$form->initialize_csrf ();
		$token = $form->csrf_token;

		$res = $form->generate_csrf_script ();
		$this->assertEquals (
			'<script>$(function(){$("form[method=\'post\']").append("<input type=\'hidden\' name=\'TOKEN\' value=\'' . $token . '\'/>");});</script>',
			$res
		);
	}

	function test_verify_csrf () {
		$form = new Form ();
		$form->initialize_csrf ();

		$this->assertFalse ($form->verify_csrf ());

		$_POST['_token_'] = $form->csrf_token;
		$this->assertTrue ($form->verify_csrf ());

		$_POST['_token_'] = '...';
		$this->assertFalse ($form->verify_csrf ());
	}
}

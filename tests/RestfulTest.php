<?php

class MockController {
	public $data;

	public function get_put_data () {
		return $this->data;
	}

	public function get_raw_post_data () {
		return $this->data;
	}
}

class RestfulTest extends PHPUnit_Framework_TestCase {
	function test_get_put_data () {
		$r = new Restful;
		$r->controller = new MockController;
		$r->controller->data = '{"foo":"bar"}';
		$decoded = json_decode ($r->controller->data);

		$this->assertEquals ($r->get_put_data (), $r->controller->data);
		$this->assertEquals ($r->get_put_data (true), $decoded);
	}

	function test_get_raw_post_data () {
		$r = new Restful;
		$r->controller = new MockController;
		$r->controller->data = '{"foo":"bar"}';
		$decoded = json_decode ($r->controller->data);

		$this->assertEquals ($r->get_raw_post_data (), $r->controller->data);
		$this->assertEquals ($r->get_raw_post_data (true), $decoded);
	}

	function test_wrap () {
		$r = new Restful;
		$data = (object) array ('foo' => 'bar');
		$correct = json_encode ((object) array ('success' => true, 'data' => $data));

		ob_start ();
		$res = $r->wrap ($data);
		$wrapped = ob_get_clean ();

		$this->assertTrue ($res);
		$this->assertEquals ($wrapped, $correct);

		$r->wrap = false;
		$correct = json_encode ($data);

		ob_start ();
		$res = $r->wrap ($data);
		$wrapped = ob_get_clean ();

		$this->assertTrue ($res);
		$this->assertEquals ($wrapped, $correct);
	}

	function test_error () {
		$r = new Restful;
		$correct = json_encode ((object) array ('success' => false, 'error' => 'Error message'));

		ob_start ();
		$res = $r->error ('Error message');
		$out = ob_get_clean ();

		$this->assertEquals (null, $res);
		$this->assertEquals ($out, $correct);
	}
}

?>
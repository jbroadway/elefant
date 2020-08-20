<?php

use PHPUnit\Framework\TestCase;

class MockController {
	public $data;
	public $status_code = false;

	public function get_put_data () {
		return $this->data;
	}

	public function get_raw_post_data () {
		return $this->data;
	}
	
	public function status_code ($code = null, $text = '') {
		$this->status_code = $code;
		return $code;
	}
}

class RestTestApi extends Restful {
	public $custom_routes = array (
		'GET bap/bop/boop' => 'get_standard',
		'ALL foo/%d' => 'foo',
		'GET bar/%s' => 'bar',
		'GET|POST one/%s/two/%s' => 'baz'
	);
	
	public function foo ($num) {
		return $num;
	}
	
	public function bar ($str) {
		return $str;
	}
	
	public function baz ($one, $two) {
		return array ($one, $two);
	}
	
	public function get_standard () {
		return 'it works';
	}
}

class RestfulTest extends TestCase {
	function test_get_params () {
		$r = new Restful;
		$r->suppress_output = true;
		$r->controller = new MockController;
		$r->controller->data = '{"foo":"bar"}';
		$decoded = json_decode ($r->controller->data);
		
		$this->assertEquals ($r->get_params (), $decoded);
		$this->assertEquals ($r->get_params (['foo']), $decoded);
		$this->assertEquals ($r->get_params (['foo' => true]), $decoded);
		
		$this->assertEquals ($r->get_params (['bar']), false);
		$this->assertEquals ($r->get_params (['bar' => true]), false);
		$this->assertEquals ($r->get_params (['bar' => false]), (object) ['bar' => null]);

		$missing_bar = (object) ['foo' => 'bar', 'bar' => null];

		$this->assertEquals ($r->get_params ([
			'foo' => true,
			'bar' => false
		]), $missing_bar);

		$r->controller->data = '{"foo":"bar","qwerty":"asdf"}';
		$decoded = json_decode ($r->controller->data);

		$this->assertEquals ($r->get_params ([
			'foo' => true,
			'qwerty' => false
		]), $decoded);

		$this->assertEquals ($r->get_params ([
			'foo' => true,
			'qwerty' => ['not empty' => true]
		]), $decoded);

		$r->controller->data = '{"foo":"","qwerty":"asdf"}';
		
		$this->assertEquals ($r->get_params ([
			'foo' => ['not empty' => true]
		]), false);
	}
	
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
	function test_rest_api () {
		$c = new Controller ();
		$c->page (new Page);

		$test_api = new RestTestApi;

		// GET bap/bop/boop should match GET bap/bop/boop
		ob_start ();
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$c->params = array ('bap', 'bop', 'boop');
		$c->restful ($test_api);
		$res = ob_get_clean ();

		ob_start ();
		$test_api->wrap ('it works');
		$exp = ob_get_clean ();

		$this->assertEquals ($exp, $res);

		// POST foo/123 should match ALL foo/%d
		ob_start ();
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$c->params = array ('foo', '123');
		$c->restful ($test_api);
		$res = ob_get_clean ();

		ob_start ();
		$test_api->wrap ('123');
		$exp = ob_get_clean ();

		$this->assertEquals ($exp, $res);

		// GET bar/hello should match GET bar/%s
		ob_start ();
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$c->params = array ('bar', 'hello');
		$c->restful ($test_api);
		$res = ob_get_clean ();

		ob_start ();
		$test_api->wrap ('hello');
		$exp = ob_get_clean ();

		$this->assertEquals ($exp, $res);

		// POST bar/hello should fail to match GET bar/%s
		ob_start ();
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$c->params = array ('bar', 'hello');
		$c->restful ($test_api);
		$res = ob_get_clean ();

		ob_start ();
		$test_api->error ('Invalid action name');
		$exp = ob_get_clean ();

		$this->assertEquals ($exp, $res);

		// GET one/and/two/and should match GET one/%s/two/%s
		ob_start ();
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$c->params = array ('one', 'and', 'two', 'and');
		$c->restful ($test_api);
		$res = ob_get_clean ();

		ob_start ();
		$test_api->wrap (array ('and', 'and'));
		$exp = ob_get_clean ();

		$this->assertEquals ($exp, $res);

		// GET standard should match get_standard()
		ob_start ();
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$c->params = array ('standard');
		$c->restful ($test_api);
		$res = ob_get_clean ();

		ob_start ();
		$test_api->wrap ('it works');
		$exp = ob_get_clean ();

		$this->assertEquals ($exp, $res);

		// POST standard should fail to match get_standard()
		ob_start ();
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$c->params = array ('standard');
		$c->restful ($test_api);
		$res = ob_get_clean ();

		ob_start ();
		$test_api->error ('Invalid action name');
		$exp = ob_get_clean ();

		$this->assertEquals ($exp, $res);

		// GET asdf/foo should fail to match all
		ob_start ();
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$c->params = array ('asdf', 'foo');
		$c->restful ($test_api);
		$res = ob_get_clean ();

		ob_start ();
		$test_api->error ('Invalid action name');
		$err = ob_get_clean ();

		$this->assertEquals ($err, $res);
	}
}

<?php

class AdminAppTest extends AppTest {
	public function test_forward () {
		$this->userAdmin ();

		$res = $this->get ('admin/forward', array ('to' => '/test'));
		$this->assertContains ('This page forwards', $res);
		$this->assertContains ('href="/test"', $res);

		$this->userAnon ();
	}

	public function test_index () {
		$res = $this->get ('admin/index');
		$this->assertContains ('Please log in to continue.', $res);
	}

	public function test_page () {
		$res = $this->get ('admin/page/index');
		$this->assertContains ('<h3>Congratulations!</h3>', $res);
	}
}

<?php

require_once ('lib/Autoloader.php');

class BlogAppTest extends AppTest {
	function test_index () {
		// Test the default output with no posts
		$res = $this->get ('blog/index');
		$this->assertContains ('No posts yet', $res);
		$this->assertNotContains ('Add Blog Post', $res);

		// Become an admin user
		$this->userAdmin ();

		// Test that the add posts link is present now
		$res = $this->get ('blog/index');
		$this->assertContains ('Add Blog Post', $res);

		// Become anonymous user again
		$this->userAnon ();
	}
}

?>
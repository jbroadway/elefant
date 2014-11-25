<?php

class PageTest extends PHPUnit_Framework_TestCase {
	function test_titles () {
		$p = new Page;
		$p->title = 'Hello';
		$this->assertEquals ('Hello', $p->menu_title);
		$this->assertEquals ('Hello', $p->window_title);

		$p->menu_title = 'Menu';
		$p->window_title = 'Window';
		$this->assertEquals ('Menu', $p->menu_title);
		$this->assertEquals ('Window', $p->window_title);
		$this->assertEquals ('Hello', $p->title);
	}

	function test_wrap_script () {
		$this->assertEquals ('<foo>', Page::wrap_script ('<foo>'));
		$this->assertEquals ("<script src=\"http://www.example.com/foo.js\"></script>\n", Page::wrap_script ('http://www.example.com/foo.js'));
		$this->assertEquals ("<script src=\"/foo.js\"></script>\n", Page::wrap_script ('/foo.js'));
		$this->assertEquals ("<script src=\"/foo.bar\"></script>\n", Page::wrap_script ('/foo.bar'));
		$this->assertEquals ("<link rel=\"stylesheet\" href=\"/foo.css\" />\n", Page::wrap_script ('/foo.css'));
	}

	function test_add_script () {
		$p = new Page;
		$p->add_script ('/foo.js');
		$this->assertEquals ("<script src=\"/foo.js\"></script>\n", $p->head);
		$p->add_script ('/bar.js', 'tail');
		$this->assertEquals ("<script src=\"/foo.js\"></script>\n", $p->head);
		$this->assertEquals ("<script src=\"/bar.js\"></script>\n", $p->tail);
	}
	
	function test_add_meta () {
		$p = new Page;

		$p->add_meta ('keywords', 'One, Two');
		$expected_head = "<meta name=\"keywords\" content=\"One, Two\" />\n";
		$this->assertEquals ($expected_head, $p->head);

		$p->add_meta ('refresh', '0;url=http://example.com/', 'http-equiv');
		$expected_head .= "<meta http-equiv=\"refresh\" content=\"0;url=http://example.com/\" />\n";
		$this->assertEquals ($expected_head, $p->head);

		$p->add_meta ('<meta charset="utf-8" />');
		$expected_head .= "<meta charset=\"utf-8\" />\n";
		$this->assertEquals ($expected_head, $p->head);
	}
}

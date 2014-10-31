<?php

class CliTest extends PHPUnit_Framework_TestCase {
	function test_out () {
		// default
		ob_start ();
		Cli::out ('Some text.');
		$out = ob_get_clean ();
		$this->assertEquals ("Some text.\n", $out);

		// no newline
		ob_start ();
		Cli::out ('Some text.', 'default', '');
		$out = ob_get_clean ();
		$this->assertEquals ("Some text.", $out);

		// info message
		ob_start ();
		Cli::out ('Some text.', 'info');
		$out = ob_get_clean ();
		$this->assertEquals ("\033[33;33mSome text.\033[0m\n", $out);

		// success message
		ob_start ();
		Cli::out ('Some text.', 'success');
		$out = ob_get_clean ();
		$this->assertEquals ("\033[0;32mSome text.\033[0m\n", $out);

		// error message
		ob_start ();
		Cli::out ('Some text.', 'error');
		$out = ob_get_clean ();
		$this->assertEquals ("\033[31;31mSome text.\033[0m\n", $out);
	}

	function test_block () {
		// single line, single tag
		ob_start ();
		Cli::block ("Output: <info>one, two</info>\n");
		$out = ob_get_clean ();
		$this->assertEquals ("Output: \033[33;33mone, two\033[0m\n", $out);

		// multiline, multi tag
		ob_start ();
		Cli::block ("<success>Yay!</success>\n<error>Oh noes</error>");
		$out = ob_get_clean ();
		$this->assertEquals ("\033[0;32mYay!\033[0m\n\033[31;31mOh noes\033[0m", $out);
	}
}

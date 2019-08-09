<?php

require_once ('lib/Functions.php');
require_once ('lib/Autoloader.php');

use PHPUnit\Framework\TestCase;

class ZipperTest extends TestCase {
	static function setUpBeforeClass (): void {
		@unlink ('zipper_test.zip');
		@rmdir_recursive ('zipper_test');
		@rmdir_recursive ('cache/zip');
	}

	static function tearDownAfterClass (): void {
		unlink ('zipper_test.zip');
		rmdir_recursive ('zipper_test');
		rmdir_recursive ('cache/zip');
	}

	function test_unzip () {
		mkdir ('zipper_test');
		file_put_contents ('zipper_test/foo.txt', 'Test');
		exec ('zip -r zipper_test.zip zipper_test');

		Zipper::unzip ('zipper_test.zip');
		$this->assertTrue (file_exists ('cache/zip/zipper_test/foo.txt'));
		$this->assertEquals ('Test', file_get_contents ('cache/zip/zipper_test/foo.txt'));
	}
}

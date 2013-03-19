<?php

require_once ('lib/Autoloader.php');
require_once ('lib/Functions.php');
require_once ('lib/I18n.php');
define ('ELEFANT_ENV', 'config');

class FileManagerTest extends PHPUnit_Framework_TestCase {
	static function setUpBeforeClass () {
		$GLOBALS['i18n'] = new I18n;
	}

	static function tearDownAfterClass () {
		unset ($GLOBALS['i18n']);

		$files = array (
			'files/touch_test.txt',
			'files/rename_test.txt',
			'files/renamed_test.txt',
			'files/move_test.txt',
			'files/design/move_test.txt'
		);
		foreach ($files as $file) {
			if (file_exists ($file)) {
				unlink ($file);
			}
		}

		if (file_exists ('files/mkdir_test')) {
			rmdir ('files/mkdir_test');
		}

		if (file_exists ('files/rmdir_recursive_test/test.txt')) {
			unlink ('files/rmdir_recursive_test/test.txt');
		}
		if (file_exists ('files/rmdir_recursive_test')) {
			rmdir ('files/rmdir_recursive_test');
		}
	}

	function test_dir () {
		$res = FileManager::dir ('');

		// the root folder with two dirs and no files
		$this->assertEquals (array ('dirs', 'files'), array_keys ($res));
		$this->assertEquals (array (), $res['files']);
		$this->assertEquals (2, count ($res['dirs']));
		$this->assertEquals ('design', $res['dirs'][0]['name']);
		$this->assertEquals ('design', $res['dirs'][0]['path']);
		$this->assertEquals ('homepage', $res['dirs'][1]['name']);
		$this->assertEquals ('homepage', $res['dirs'][1]['path']);

		// a folder with files in it
		$res = FileManager::dir ('homepage');
		$this->assertEquals (0, count ($res['dirs']));
		$this->assertEquals (4, count ($res['files']));
		$this->assertEquals ('photo1.jpg', $res['files'][0]['name']);
		$this->assertEquals ('homepage/photo1.jpg', $res['files'][0]['path']);
		$this->assertEquals ('photo2.jpg', $res['files'][1]['name']);
		$this->assertEquals ('homepage/photo2.jpg', $res['files'][1]['path']);
		$this->assertEquals ('photo3.jpg', $res['files'][2]['name']);
		$this->assertEquals ('homepage/photo3.jpg', $res['files'][2]['path']);
		$this->assertEquals ('photo4.jpg', $res['files'][3]['name']);
		$this->assertEquals ('homepage/photo4.jpg', $res['files'][3]['path']);

		// check invalid folder
		$res = FileManager::dir ('foooooo');
		$this->assertFalse ($res);
		$this->assertEquals ('Invalid folder name', FileManager::error ());
	}

	function test_touch_and_unlink () {
		$this->assertFalse (file_exists ('files/touch_test.txt'));

		$res = FileManager::touch ('touch_test.txt');
		$this->assertTrue ($res);
		$this->assertTrue (file_exists ('files/touch_test.txt'));

		$res = FileManager::touch ('../invalid');
		$this->assertFalse ($res);
		$this->assertEquals ('Invalid folder', FileManager::error ());

		$mtime = filemtime ('files/touch_test.txt');
		sleep (1);
		FileManager::touch ('touch_test.txt');
		clearstatcache ('files/touch_test.txt');
		$mtime2 = filemtime ('files/touch_test.txt');
		$this->assertNotEquals ($mtime, $mtime2);

		$res = FileManager::unlink ('touch_test.txt');
		$this->assertTrue ($res);

		$this->assertFalse (file_exists ('files/touch_test.txt'));
	}

	function test_rename () {
		FileManager::touch ('rename_test.txt');
		$this->assertTrue (file_exists ('files/rename_test.txt'));

		$res = FileManager::rename ('rename_test.txt', 'renamed_test.txt');
		$this->assertTrue ($res);

		$this->assertFalse (file_exists ('files/rename_test.txt'));
		$this->assertTrue (file_exists ('files/renamed_test.txt'));
		
		FileManager::unlink ('renamed_test.txt');
	}

	function test_move () {
		FileManager::touch ('move_test.txt');
		$this->assertTrue (file_exists ('files/move_test.txt'));
		
		$res = FileManager::move ('move_test.txt', 'design');
		$this->assertTrue ($res);
		
		$this->assertFalse (file_exists ('files/move_test.txt'));
		$this->assertTrue (file_exists ('files/design/move_test.txt'));

		$res = FileManager::move ('design/move_test.txt', '..');
		$this->assertFalse ($res);
		$this->assertEquals ('Invalid folder', FileManager::error ());

		$res = FileManager::move ('design/move_test.txt', '');
		$this->assertTrue ($res);
		
		$this->assertTrue (file_exists ('files/move_test.txt'));
		$this->assertFalse (file_exists ('files/design/move_test.txt'));

		FileManager::unlink ('move_test.txt');
	}

	function test_mkdir_and_rmdir () {
		$res = FileManager::mkdir ('@#$%');
		$this->assertFalse ($res);
		$this->assertEquals ('Invalid folder name', FileManager::error ());

		$res = FileManager::mkdir ('../foo');
		$this->assertFalse ($res);
		$this->assertEquals ('Invalid location', FileManager::error ());

		$res = FileManager::mkdir ('design');
		$this->assertFalse ($res);
		$this->assertEquals ('Folder already exists design', FileManager::error ());

		$res = FileManager::mkdir ('mkdir_test');
		$this->assertTrue ($res);
		$this->assertTrue (is_dir ('files/mkdir_test'));

		$res = FileManager::rmdir ('mkdir_fake_test');
		$this->assertFalse ($res);
		$this->assertEquals ('Invalid folder name', FileManager::error ());

		$res = FileManager::rmdir ('mkdir_test');
		$this->assertTrue ($res);
		$this->assertFalse (is_dir ('files/mkdir_test'));
	}

	function test_rmdir_recursive () {
		$res = FileManager::mkdir ('rmdir_recursive_test');
		$this->assertTrue ($res);
		$this->assertTrue (is_dir ('files/rmdir_recursive_test'));
		
		$res = FileManager::touch ('rmdir_recursive_test/test.txt');
		$this->assertTrue ($res);
		$this->assertTrue (file_exists ('files/rmdir_recursive_test/test.txt'));

		$res = FileManager::rmdir ('rmdir_recursive_test');
		$this->assertFalse ($res);
		$this->assertEquals ('Folder must be empty', FileManager::error ());
		
		$res = FileManager::rmdir ('rmdir_recursive_test', true);
		$this->assertTrue ($res);
		$this->assertFalse (is_dir ('files/rmdir_recursive_test'));
	}
}

?>
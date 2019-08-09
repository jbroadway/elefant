<?php

require_once ('apps/admin/lib/Functions.php');

use PHPUnit\Framework\TestCase;

class AdminFunctionsTest extends TestCase {
	static function setUpBeforeClass (): void {
		if (file_exists ('cache/html')) {
			rmdir_recursive ('cache/html');
		}
	}

	function test_embed_filter_and_lookup () {
		// Test filter returns non-empty ID
		$html = 'testing<br />';
		$id = admin_embed_filter ($html);
		$this->assertGreaterThan (0, strlen ($id));

		// Perform lookup
		$this->assertEquals ($html, admin_embed_lookup ($id));

		// Filter in reverse, same as lookup
		$this->assertEquals ($html, admin_embed_filter ($id, true));
	}

	function test_get_layouts () {
		conf ('General', 'default_layout', 'default');
		$layouts = admin_get_layouts ();
		$this->assertTrue (is_array ($layouts));
		$this->assertTrue (in_array ('admin', $layouts));
		$this->assertTrue (in_array ('minimal', $layouts));
	}

	function test_layout_exists () {
		$this->assertTrue (admin_layout_exists ('admin'));
		$this->assertTrue (admin_layout_exists ('minimal'));
		$this->assertFalse (admin_layout_exists ('this-is-a-nonexistent-layout'));
	}
}

<?php

require_once ('lib/Autoloader.php');
require_once ('conf/version.php');

class GithubFetcherTest extends PHPUnit_Framework_TestCase {
	public static $g;
	public static $t;

	function test_construct () {
		self::$g = new GithubFetcher ('git://github.com/jbroadway/githubfetcher_test.git');
		$this->assertEquals ('jbroadway', self::$g->user);
		$this->assertEquals ('githubfetcher_test', self::$g->project);
	}

	function test_sha () {
		$this->assertEquals ('4e34fad4ccc02765f196d5664ac7ddbeff025827', self::$g->sha ());
	}

	function test_tree () {
		$tree = self::$g->tree ();
		$this->assertEquals (3, count ($tree));
		foreach ($tree as $item) {
			if ($item->path === 'one') {
				$this->assertEquals ('tree', $item->type);
			} else {
				$this->assertEquals ('blob', $item->type);
			}
		}
		self::$t = $tree;
	}

	function test_get () {
		foreach (self::$t as $item) {
			if ($item->path === 'two.txt') {
				$this->assertEquals ('four', self::$g->get ($item));
			} elseif ($item->path === 'one/three.txt') {
				$this->assertEquals ('five', self::$g->get ($item->sha));
			}
		}
	}
}

?>
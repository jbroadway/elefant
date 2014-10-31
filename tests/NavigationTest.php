<?php

class NavigationTest extends PHPUnit_Framework_TestCase {
	static function setUpBeforeClass () {
		DB::open (array ('master' => true, 'driver' => 'sqlite', 'file' => ':memory:'));
		DB::$prefix = 'elefant_';
		$sql = sql_split ('
			create table #prefix#webpage (
				id char(72) not null primary key,
				title char(72) not null,
				menu_title char(72) not null,
				window_title char(72) not null,
				access char(12) not null,
				layout char(48) not null,
				description text,
				keywords text,
				body text
			);
			insert into #prefix#webpage (id, title, menu_title, window_title, access, layout, description, keywords, body) values ("index", "Welcome to Elefant", "Home", "", "public", "default", "", "", \'<table><tbody><tr><td><h3>Congratulations!</h3>You have successfully installed Elefant, the refreshingly simple new PHP web framework and CMS.</td><td><h3>Getting Started</h3>To log in as an administrator and edit pages, write a blog post, or upload files, go to <a href="/admin">/admin</a>.</td><td><h3>Developers</h3>Documentation, source code and issue tracking can be found at <a href="http://github.com/jbroadway/elefant">github.com/jbroadway/elefant</a></td></tr></tbody></table>\');
		');
		foreach ($sql as $query) {
			if (! DB::execute ($query)) {
				die (DB::error ());
			}
		}
	}

	function test_single_node () {
		$n = new Navigation;

		$this->assertEquals ('conf/navigation.json', $n->file);
	}

	function test_adding_node () {
		$n = new Navigation;

		$blog_node = (object) array (
			'data' => 'Blog',
			'attr' => (object) array (
				'id' => 'blog',
				'sort' => 0
			)
		);

		/**
		 * Add blog page.
		 */
		$n->add ($blog_node, 'index');

		/**
		 * Should have second id now:
		 *
		 * index
		 * - blog
		 */
		$this->assertEquals ($n->get_all_ids (), array ('index', 'blog'));
		
		// Remove and re-add index
		$n->remove ('index');
		$this->assertEquals ($n->get_all_ids (), array ());

		$n->add ('index');
		$index_node = $n->node ('index');

		$expected_index = (object) array (
			'data' => 'Home',
			'attr' => (object) array (
				'id' => 'index',
				'sort' => 0
			)
		);

		/*
		 * Should have index node with title 'Home' from the database.
		 */
		$this->assertEquals ($expected_index, $index_node);
	}
}

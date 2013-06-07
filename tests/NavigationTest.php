<?php

class NavigationTest extends PHPUnit_Framework_TestCase {
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

?>
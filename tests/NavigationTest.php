<?php

class NavigationTest extends PHPUnit_Framework_TestCase {
	protected static $n;
	protected static $about_node;
	protected static $blog_node;
	protected static $contact_node;

	function test_single_node () {
		self::$n = new Navigation;

		/**
		 * This is the tree on first install, just an index page.
		 */
		self::$n->tree = array (
			(object) array (
				'data' => 'Home',
				'attr' => (object) array (
					'id' => 'index',
					'sort' => 0
				)
			)
		);

		/**
		 * Should have only one id:
		 *
		 * index
		 */
		self::$this->assertEquals (self::$n->get_all_ids (), array ('index'));
	}

	function test_adding_node () {
		self::$blog_node = (object) array (
			'data' => 'Blog',
			'attr' => (object) array (
				'id' => 'blog',
				'sort' => 0
			)
		);

		/**
		 * Add blog page.
		 */
		self::$n->add (self::$blog_node, 'index');

		/**
		 * Should have second id now:
		 *
		 * index
		 * - blog
		 */
		$this->assertEquals (self::$n->get_all_ids (), array ('index', 'blog'));
	}

	function test_adding_subnode () {
		self::$about_node = (object) array (
			'data' => 'About',
			'attr' => (object) array (
				'id' => 'about',
				'sort' => 0
			)
		);

		/**
		 * Add about page under blog.
		 */
		self::$n->add (self::$about_node, 'blog');

		/**
		 * Should have third id now under blog:
		 *
		 * index
		 * - blog
		 *   - about
		 */
		$this->assertEquals (self::$n->get_all_ids (), array ('index', 'blog', 'about'));

		// Make sure about is under blog.
		$this->assertEquals (self::$n->parent ('about')->attr->id, self::$blog_node->attr->id);
	}

	function test_find_node () {
		// Find the about node and verify it.
		$this->assertEquals (self::$n->node ('about')->attr->id, self::$about_node->attr->id);
	}

	function test_paths () {
		// Test all paths
		$this->assertEquals (self::$n->path ('index'), array ('index'));
		$this->assertEquals (self::$n->path ('blog'), array ('index', 'blog'));
		$this->assertEquals (self::$n->path ('about'), array ('index', 'blog', 'about'));
		
		// Test paths with titles
		$this->assertEquals (self::$n->path ('blog', true), array ('index' => 'Home', 'blog' => 'Blog'));
	}

	function test_sections () {
		// Test sections
		$this->assertEquals (self::$n->sections (), array ('index', 'blog'));
	}

	function test_remove () {
		// Remove about and verify.
		self::$n->remove ('about');
		$this->assertFalse (isset (self::$n->node ('blog')->children));
		$this->assertNull (self::$n->node ('about'));
		$this->assertEquals (self::$n->get_all_ids (), array ('index', 'blog'));
	}

	function test_remove_with_children () {
		/**
		 * Add about and remove blog and verify.
		 *
		 * Structure:
		 *
		 * index
		 * - blog
		 *   - about
		 */
		self::$n->add (self::$about_node, 'blog');
		$this->assertEquals (self::$n->get_all_ids (), array ('index', 'blog', 'about'));
		self::$n->remove ('blog');
		$this->assertNull (self::$n->node ('about'));
		$this->assertNull (self::$n->node ('blog'));
		$this->assertEquals (self::$n->get_all_ids (), array ('index'));
	}

	function test_add_remove () {
		/**
		 * Add blog to root and verify add/remove root nodes.
		 *
		 * Structure:
		 *
		 * index
		 * about
		 */
		self::$about_node = (object) array (
			'data' => 'About',
			'attr' => (object) array (
				'id' => 'about',
				'sort' => 0
			)
		);

		self::$n->add (self::$about_node);
		$this->assertEquals (self::$n->get_all_ids (), array ('index', 'about'));
		$this->assertEquals (self::$n->node ('about')->attr->sort, 1);
		self::$n->remove ('about');
		$this->assertEquals (count (self::$n->tree), 1);
	}

	function test_remove_non_recursive () {
		/**
		 * Add blog and about and contact nodes and test remove() non-recursive.
		 *
		 * Structure:
		 *
		 * index
		 * about
		 * - contact
		 * - blog
		 */
		self::$about_node = (object) array (
			'data' => 'About',
			'attr' => (object) array (
				'id' => 'about',
				'sort' => 0
			)
		);

		self::$contact_node = (object) array (
			'data' => 'Contact',
			'attr' => (object) array (
				'id' => 'contact',
				'sort' => 0
			)
		);

		self::$blog_node = (object) array (
			'data' => 'Blog',
			'attr' => (object) array (
				'id' => 'blog',
				'sort' => 0
			)
		);

		self::$n->add (self::$about_node);
		self::$n->add (self::$contact_node, 'about');
		self::$n->add (self::$blog_node, 'about');
		$this->assertEquals (self::$n->get_all_ids (), array ('index', 'about', 'contact', 'blog'));

		self::$n->remove ('about', false);
		$this->assertEquals (self::$n->get_all_ids (), array ('index', 'contact', 'blog'));
		$this->assertEquals (self::$n->parent ('contact'), null);
		$this->assertEquals (self::$n->parent ('blog'), null);
		self::$n->remove ('contact');
		self::$n->remove ('blog');
	}

	function test_remove_path () {
		/**
		 * Add node in two places and test remove_path().
		 *
		 * Structure:
		 *
		 * index
		 * - contact
		 *   - about
		 * about
		 */
		self::$about_node = (object) array (
			'data' => 'About',
			'attr' => (object) array (
				'id' => 'about',
				'sort' => 0
			)
		);

		self::$contact_node = (object) array (
			'data' => 'Contact',
			'attr' => (object) array (
				'id' => 'contact',
				'sort' => 0
			)
		);

		self::$n->add (self::$about_node);
		self::$n->add (self::$contact_node, 'index');
		self::$n->add (self::$about_node, 'contact');
		$this->assertEquals (self::$n->get_all_ids (), array ('index', 'contact', 'about', 'about'));
		self::$n->remove_path (array ('index', 'contact', 'about'));
		$contact = self::$n->node ('contact');
		$this->assertFalse (isset ($contact->children));
		self::$n->remove ('contact');
		self::$n->remove ('about');
	}

	function test_move () {
		/**
		 * Add blog and about and contact nodes and test move().
		 *
		 * Structure:
		 *
		 * index
		 * - about
		 *   - blog
		 * - contact
		 */
		self::$about_node = (object) array (
			'data' => 'About',
			'attr' => (object) array (
				'id' => 'about',
				'sort' => 0
			)
		);

		self::$contact_node = (object) array (
			'data' => 'Contact',
			'attr' => (object) array (
				'id' => 'contact',
				'sort' => 0
			)
		);

		self::$blog_node = (object) array (
			'data' => 'Blog',
			'attr' => (object) array (
				'id' => 'blog',
				'sort' => 0
			)
		);

		self::$n->add (self::$about_node, 'index');
		self::$n->add (self::$contact_node, 'index');
		self::$n->add (self::$blog_node, 'about');
		$this->assertEquals (self::$n->get_all_ids (), array ('index', 'about', 'blog', 'contact'));

		/**
		 * Move contact under about. New structure:
		 *
		 * index
		 * - about
		 *   - blog
		 *   - contact
		 */
		self::$n->move ('contact', 'about');
		$this->assertEquals (self::$n->parent ('contact')->attr->id, 'about');

		/**
		 * Move contact under blog. New structure:
		 *
		 * index
		 * - about
		 *   - blog
		 *     - contact
		 */
		self::$n->move ('contact', 'blog');
		$this->assertEquals (self::$n->parent ('contact')->attr->id, 'blog');
		$this->assertEquals (self::$n->parent ('blog')->attr->id, 'about');
		$this->assertEquals (self::$n->parent ('about')->attr->id, 'index');

		/**
		 * Move blog to top. New structure:
		 *
		 * index
		 * - about
		 * blog
		 * - contact
		 */
		self::$n->move ('blog', false);
		$this->assertEquals (self::$n->parent ('contact')->attr->id, 'blog');
		$this->assertEquals (self::$n->parent ('blog'), null);

		/**
		 * Move blog to after about under index. New structure:
		 *
		 * index
		 * - about
		 * - blog
		 *   - contact
		 */
		self::$n->move ('blog', 'about', 'after');
		$this->assertEquals (self::$n->parent ('blog')->attr->id, 'index');
		$this->assertEquals (self::$n->parent ('contact')->attr->id, 'blog');
		$this->assertEquals (self::$n->node ('about')->attr->sort, 0);
		$this->assertEquals (self::$n->node ('blog')->attr->sort, 1);

		/**
		 * Move blog to after about under index. New structure:
		 *
		 * index
		 * - about
		 * - contact
		 * - blog
		 */
		self::$n->move ('contact', 'blog', 'before');
		$this->assertEquals (self::$n->parent ('contact')->attr->id, 'index');
		$this->assertEquals (self::$n->node ('about')->attr->sort, 0);
		$this->assertEquals (self::$n->node ('contact')->attr->sort, 1);
		$this->assertEquals (self::$n->node ('blog')->attr->sort, 2);

		/**
		 * Move blog to before index. New structure:
		 *
		 * blog
		 * index
		 * - about
		 * - contact
		 */
		self::$n->move ('blog', 'index', 'before');
		$this->assertEquals (self::$n->parent ('blog'), null);
		$this->assertEquals (self::$n->node ('blog')->attr->sort, 0);
		$this->assertEquals (self::$n->node ('index')->attr->sort, 1);

		/**
		 * Move blog to after index. New structure:
		 *
		 * index
		 * - about
		 * - contact
		 * blog
		 */
		self::$n->move ('blog', 'index', 'after');
		$this->assertEquals (self::$n->parent ('blog'), null);
		$this->assertEquals (self::$n->node ('index')->attr->sort, 0);
		$this->assertEquals (self::$n->node ('blog')->attr->sort, 1);

		/**
		 * Move index to after blog. New structure:
		 *
		 * blog
		 * index
		 * - about
		 * - contact
		 */
		self::$n->move ('index', 'blog', 'after');
		$this->assertEquals (self::$n->parent ('blog'), null);
		$this->assertEquals (self::$n->node ('blog')->attr->sort, 0);
		$this->assertEquals (self::$n->node ('index')->attr->sort, 1);
		$this->assertEquals (count (self::$n->node ('index')->children), 2);

		/**
		 * Move index to before blog. New structure:
		 *
		 * index
		 * - about
		 * - contact
		 * blog
		 */
		self::$n->move ('index', 'blog', 'before');
		$this->assertEquals (self::$n->parent ('blog'), null);
		$this->assertEquals (self::$n->node ('index')->attr->sort, 0);
		$this->assertEquals (self::$n->node ('blog')->attr->sort, 1);
		$this->assertEquals (count (self::$n->node ('index')->children), 2);
	}
}

?>
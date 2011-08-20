<?php

require_once ('apps/navigation/lib/Navigation.php');

class NavigationTest extends PHPUnit_Framework_TestCase {
	function test_navigation () {
		$n = new Navigation;

		/**
		 * This is the tree on first install, just an index page.
		 */
		$n->tree = array (
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
		$this->assertEquals ($n->get_all_ids (), array ('index'));

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

		$about_node = (object) array (
			'data' => 'About',
			'attr' => (object) array (
				'id' => 'about',
				'sort' => 0
			)
		);

		/**
		 * Add about page under blog.
		 */
		$n->add ($about_node, 'blog');

		/**
		 * Should have third id now under blog:
		 *
		 * index
		 * - blog
		 *   - about
		 */
		$this->assertEquals ($n->get_all_ids (), array ('index', 'blog', 'about'));

		// Make sure about is under blog.
		$this->assertEquals ($n->parent ('about')->attr->id, $blog_node->attr->id);

		// Find the about node and verify it.
		$this->assertEquals ($n->node ('about')->attr->id, $about_node->attr->id);

		// Test all paths
		$this->assertEquals ($n->path ('index'), array ('index'));
		$this->assertEquals ($n->path ('blog'), array ('index', 'blog'));
		$this->assertEquals ($n->path ('about'), array ('index', 'blog', 'about'));

		// Test sections
		$this->assertEquals ($n->sections (), array ('index', 'blog'));

		// Remove about and verify.
		$n->remove ('about');
		$this->assertFalse (isset ($n->node ('blog')->children));
		$this->assertNull ($n->node ('about'));
		$this->assertEquals ($n->get_all_ids (), array ('index', 'blog'));

		/**
		 * Add about and remove blog and verify.
		 *
		 * Structure:
		 *
		 * index
		 * - blog
		 *   - about
		 */
		$n->add ($about_node, 'blog');
		$this->assertEquals ($n->get_all_ids (), array ('index', 'blog', 'about'));
		$n->remove ('blog');
		$this->assertNull ($n->node ('about'));
		$this->assertNull ($n->node ('blog'));
		$this->assertEquals ($n->get_all_ids (), array ('index'));

		/**
		 * Add blog to root and verify add/remove root nodes.
		 *
		 * Structure:
		 *
		 * index
		 * about
		 */
		$about_node = (object) array (
			'data' => 'About',
			'attr' => (object) array (
				'id' => 'about',
				'sort' => 0
			)
		);

		$n->add ($about_node);
		$this->assertEquals ($n->get_all_ids (), array ('index', 'about'));
		$this->assertEquals ($n->node ('about')->attr->sort, 1);
		$n->remove ('about');
		$this->assertEquals (count ($n->tree), 1);

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
		$about_node = (object) array (
			'data' => 'About',
			'attr' => (object) array (
				'id' => 'about',
				'sort' => 0
			)
		);

		$contact_node = (object) array (
			'data' => 'Contact',
			'attr' => (object) array (
				'id' => 'contact',
				'sort' => 0
			)
		);

		$blog_node = (object) array (
			'data' => 'Blog',
			'attr' => (object) array (
				'id' => 'blog',
				'sort' => 0
			)
		);

		$n->add ($about_node);
		$n->add ($contact_node, 'about');
		$n->add ($blog_node, 'about');
		$this->assertEquals ($n->get_all_ids (), array ('index', 'about', 'contact', 'blog'));

		$n->remove ('about', false);
		$this->assertEquals ($n->get_all_ids (), array ('index', 'contact', 'blog'));
		$this->assertEquals ($n->parent ('contact'), null);
		$this->assertEquals ($n->parent ('blog'), null);
		$n->remove ('contact');
		$n->remove ('blog');

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
		$about_node = (object) array (
			'data' => 'About',
			'attr' => (object) array (
				'id' => 'about',
				'sort' => 0
			)
		);

		$contact_node = (object) array (
			'data' => 'Contact',
			'attr' => (object) array (
				'id' => 'contact',
				'sort' => 0
			)
		);

		$n->add ($about_node);
		$n->add ($contact_node, 'index');
		$n->add ($about_node, 'contact');
		$this->assertEquals ($n->get_all_ids (), array ('index', 'contact', 'about', 'about'));
		$n->remove_path (array ('index', 'contact', 'about'));
		$contact = $n->node ('contact');
		$this->assertFalse (isset ($contact->children));
		$n->remove ('contact');
		$n->remove ('about');

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
		$about_node = (object) array (
			'data' => 'About',
			'attr' => (object) array (
				'id' => 'about',
				'sort' => 0
			)
		);

		$contact_node = (object) array (
			'data' => 'Contact',
			'attr' => (object) array (
				'id' => 'contact',
				'sort' => 0
			)
		);

		$blog_node = (object) array (
			'data' => 'Blog',
			'attr' => (object) array (
				'id' => 'blog',
				'sort' => 0
			)
		);

		$n->add ($about_node, 'index');
		$n->add ($contact_node, 'index');
		$n->add ($blog_node, 'about');
		$this->assertEquals ($n->get_all_ids (), array ('index', 'about', 'blog', 'contact'));

		/**
		 * Move contact under about. New structure:
		 *
		 * index
		 * - about
		 *   - blog
		 *   - contact
		 */
		$n->move ('contact', 'about');
		$this->assertEquals ($n->parent ('contact')->attr->id, 'about');

		/**
		 * Move contact under blog. New structure:
		 *
		 * index
		 * - about
		 *   - blog
		 *     - contact
		 */
		$n->move ('contact', 'blog');
		$this->assertEquals ($n->parent ('contact')->attr->id, 'blog');
		$this->assertEquals ($n->parent ('blog')->attr->id, 'about');
		$this->assertEquals ($n->parent ('about')->attr->id, 'index');

		/**
		 * Move blog to top. New structure:
		 *
		 * index
		 * - about
		 * blog
		 * - contact
		 */
		$n->move ('blog', false);
		$this->assertEquals ($n->parent ('contact')->attr->id, 'blog');
		$this->assertEquals ($n->parent ('blog'), null);

		/**
		 * Move blog to after about under index. New structure:
		 *
		 * index
		 * - about
		 * - blog
		 *   - contact
		 */
		$n->move ('blog', 'about', 'after');
		$this->assertEquals ($n->parent ('blog')->attr->id, 'index');
		$this->assertEquals ($n->parent ('contact')->attr->id, 'blog');
		$this->assertEquals ($n->node ('about')->attr->sort, 0);
		$this->assertEquals ($n->node ('blog')->attr->sort, 1);

		/**
		 * Move blog to after about under index. New structure:
		 *
		 * index
		 * - about
		 * - contact
		 * - blog
		 */
		$n->move ('contact', 'blog', 'before');
		$this->assertEquals ($n->parent ('contact')->attr->id, 'index');
		$this->assertEquals ($n->node ('about')->attr->sort, 0);
		$this->assertEquals ($n->node ('contact')->attr->sort, 1);
		$this->assertEquals ($n->node ('blog')->attr->sort, 2);

		/**
		 * Move blog to before index. New structure:
		 *
		 * blog
		 * index
		 * - about
		 * - contact
		 */
		$n->move ('blog', 'index', 'before');
		$this->assertEquals ($n->parent ('blog'), null);
		$this->assertEquals ($n->node ('blog')->attr->sort, 0);
		$this->assertEquals ($n->node ('index')->attr->sort, 1);

		/**
		 * Move blog to after index. New structure:
		 *
		 * index
		 * - about
		 * - contact
		 * blog
		 */
		$n->move ('blog', 'index', 'after');
		$this->assertEquals ($n->parent ('blog'), null);
		$this->assertEquals ($n->node ('index')->attr->sort, 0);
		$this->assertEquals ($n->node ('blog')->attr->sort, 1);

		/**
		 * Move index to after blog. New structure:
		 *
		 * blog
		 * index
		 * - about
		 * - contact
		 */
		$n->move ('index', 'blog', 'after');
		$this->assertEquals ($n->parent ('blog'), null);
		$this->assertEquals ($n->node ('blog')->attr->sort, 0);
		$this->assertEquals ($n->node ('index')->attr->sort, 1);
		$this->assertEquals (count ($n->node ('index')->children), 2);

		/**
		 * Move index to before blog. New structure:
		 *
		 * index
		 * - about
		 * - contact
		 * blog
		 */
		$n->move ('index', 'blog', 'before');
		$this->assertEquals ($n->parent ('blog'), null);
		$this->assertEquals ($n->node ('index')->attr->sort, 0);
		$this->assertEquals ($n->node ('blog')->attr->sort, 1);
		$this->assertEquals (count ($n->node ('index')->children), 2);
	}
}

?>
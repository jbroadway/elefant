<?php

require_once ('apps/navigation/lib/Navigation.php');

class NavigationTest extends PHPUnit_Framework_TestCase {
	function test_navigation () {
		$n = new Navigation;

		// this is the tree on first install
		$n->tree = array (
			(object) array (
				'data' => 'Home',
				'attr' => (object) array (
					'id' => 'index',
					'sort' => 0
				)
			)
		);

		// should have only one id
		$this->assertEquals ($n->get_all_ids (), array ('index'));

		$blog_node = (object) array (
			'data' => 'Blog',
			'attr' => (object) array (
				'id' => 'blog',
				'sort' => 0
			)
		);

		// add blog page
		$n->add ($blog_node, 'index');

		// should have second id now
		$this->assertEquals ($n->get_all_ids (), array ('index', 'blog'));

		$about_node = (object) array (
			'data' => 'About',
			'attr' => (object) array (
				'id' => 'about',
				'sort' => 0
			)
		);

		// add about page under blog
		$n->add ($about_node, 'blog');

		// should have third id now
		$this->assertEquals ($n->get_all_ids (), array ('index', 'blog', 'about'));

		// make sure about is under blog
		$this->assertEquals ($n->find_parent ('about')->attr->id, $blog_node->attr->id);

		// find the about node and verify it
		$this->assertEquals ($n->find_ref ('about')->attr->id, $about_node->attr->id);

		// test all paths
		$this->assertEquals ($n->find_path ('index'), array ('index'));
		$this->assertEquals ($n->find_path ('blog'), array ('index', 'blog'));
		$this->assertEquals ($n->find_path ('about'), array ('index', 'blog', 'about'));

		// remove about and verify
		$n->remove ('about');
		$this->assertFalse (isset ($n->find_ref ('blog')->children));
		$this->assertNull ($n->find_ref ('about'));
		$this->assertEquals ($n->get_all_ids (), array ('index', 'blog'));

		// add about and remove blog and verify
		$n->add ($about_node, 'blog');
		$this->assertEquals ($n->get_all_ids (), array ('index', 'blog', 'about'));
		$n->remove ('blog');
		$this->assertNull ($n->find_ref ('about'));
		$this->assertNull ($n->find_ref ('blog'));
		$this->assertEquals ($n->get_all_ids (), array ('index'));

		// add blog to root and verify add/remove root nodes
		$about_node = (object) array (
			'data' => 'About',
			'attr' => (object) array (
				'id' => 'about',
				'sort' => 0
			)
		);

		$n->add ($about_node);
		$this->assertEquals ($n->get_all_ids (), array ('index', 'about'));
		$this->assertEquals ($n->find_ref ('about')->attr->sort, 1);
		$n->remove ('about');
		$this->assertEquals (count ($n->tree), 1);

		// add blog and about and contact nodes and test remove() non-recursive
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
		$this->assertEquals ($n->find_parent ('contact'), null);
		$this->assertEquals ($n->find_parent ('blog'), null);
		$n->remove ('contact');
		$n->remove ('blog');

		// add node in two places and test remove_path()
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
		$contact = $n->find_ref ('contact');
		$this->assertFalse (isset ($contact->children));
		$n->remove ('contact');
		$n->remove ('about');

		// add blog and about and contact nodes and test move()
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

		$n->move ('contact', 'about');
		$this->assertEquals ($n->find_parent ('contact')->attr->id, $about_node->attr->id);
	}
}

?>
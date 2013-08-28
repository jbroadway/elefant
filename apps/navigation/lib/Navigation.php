<?php

/**
 * Elefant CMS - http://www.elefantcms.com/
 *
 * Copyright (c) 2011 Johnny Broadway
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Handles managing the navigation tree as a jstree-compatible
 * json object. Items in the tree have the following structure:
 *
 *     {"data":"Title","attr":{"id":"page-id","sort":0}}
 *
 * Item that have children will have an additional `children`
 * property that is an array of other items.
 *
 * Usage:
 *
 *     $n = new Navigation;
 *     // $n->tree contains data from conf/navigation.tree
 *
 *     $n->add ('index');
 *     $n->add ('blog', 'index');
 *
 *     $node = $n->node ('blog');
 *     // {"data":"Blog","attr":{"id":"blog","sort":0}}
 *
 *     $path = $n->path ('blog');
 *     // array ('index', 'blog')
 *
 *     $n->move ('blog', 'index', 'before');
 *     $ids = $n->get_all_ids ();
 *     // array ('blog', 'index')
 *
 *     $n->remove ('blog');
 *
 *     // save to conf/navigation.json or the file configured in global config
 *     $n->save ();
 */
class Navigation extends Tree {
	/**
	 * Constructor method. Decodes the navigation tree from the specified
	 * file in JSON format, or defaults to `conf ('Paths', 'navigation_json')`
	 * if no file is specified.
	 */
	public function __construct ($file = null) {
        $file  = $file ? $file : conf ('Paths','navigation_json');
        parent::__construct ($file);
	}

	/**
	 * Overrides `Tree::add()` to fetch the page title from the database if only
	 * the ID is passed.
	 */
	public function add ($id, $parent = false, $title = false) {
		if (! is_object ($id) && $title === false) {
			$pg = DB::single ('select title, menu_title from #prefix#webpage where id = ?', $id);
			$title = (! empty ($pg->menu_title)) ? $pg->menu_title : $pg->title;
			$id = (object) array (
				'data' => $title,
				'attr' => (object) array (
					'id' => $id,
					'sort' => 0
				)
			);
		}

		parent::add ($id, $parent, $title);
	}
}

/**
 * Alias of Navigation::sections() for embed dialog.
 */
function navigation_get_sections () {
	$n = new Navigation;
	return $n->sections ();
}

?>
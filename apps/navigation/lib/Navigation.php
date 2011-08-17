<?php

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
 *     // save to conf/navigation.json
 *     $n->save ();
 */
class Navigation {
	var $file = 'conf/navigation.json';
	var $tree = array ();
	var $_ref = false;
	var $error = false;

	function __construct ($file = 'conf/navigation.json') {
		$this->file = $file;
		$this->tree = json_decode (file_get_contents ($file));
	}

	/**
	 * Get all page ids from the tree.
	 */
	function get_all_ids ($tree = false) {
		if (! $tree) {
			$tree = $this->tree;
		}

		$ids = array ();
		foreach ($tree as $item) {
			$ids[] = $item->attr->id;
			if (isset ($item->children)) {
				$ids = array_merge ($ids, $this->get_all_ids ($item->children));
			}
		}
		return $ids;
	}

	/**
	 * Find a specific node in the tree.
	 */
	function node ($id, $tree = false) {
		if (! $tree) {
			$tree = $this->tree;
		}
		
		foreach ($tree as $item) {
			if ($item->attr->id == $id) {
				return $item;
			}
			if (isset ($item->children)) {
				$res = $this->node ($id, $item->children);
				if ($res) {
					return $res;
				}
			}
		}
		return null;
	}

	/**
	 * Find the parent of a specific node in the tree.
	 */
	function parent ($id, $tree = false) {
		if (! $tree) {
			$tree = $this->tree;
		}

		$parent = null;		
		foreach ($tree as $item) {
			if ($item->attr->id == $id) {
				// found item itself, should have found parent by now
				return null;
			}
			if (isset ($item->children)) {
				foreach ($item->children as $child) {
					if ($child->attr->id == $id) {
						// we're on the parent!
						return $item;
					}
				}
				$parent = $this->parent ($id, $item->children);
			}
		}
		return $parent;
	}

	/**
	 * Find the path to a specific node in the tree, including the node itself.
	 */
	function path ($id, $tree = false) {
		if (! $tree) {
			$tree = $this->tree;
		}

		foreach ($tree as $item) {
			if ($item->attr->id == $id) {
				return array ($id);
			} elseif (isset ($item->children)) {
				$res = $this->path ($id, $item->children);
				if ($res) {
					return array_merge (array ($item->attr->id), $res);
				}
			}
		}

		return null;
	}

	/**
	 * Add a page to the tree under the specified parent.
	 * $id can be a page ID or a node object.
	 */
	function add ($id, $parent = false) {
		if (is_object ($id)) {
			$new_page = $id;
		} else {
			$pg = db_single ('select title, menu_title from webpage where id = ?', $id);
			$title = (! empty ($pg->menu_title)) ? $pg->menu_title : $pg->title;
			$new_page = (object) array (
				'data' => $title,
				'attr' => (object) array (
					'id' => $id,
					'sort' => 0
				)
			);
		}

		// locate $parent and add child
		if ($parent) {
			$ref = $this->node ($parent);
			if (! isset ($ref->children)) {
				$ref->children = array ();
			}
			$new_page->attr->sort = count ($ref->children);
			$ref->children[] = $new_page;
			$ref->state = 'open';
		} else {
			$new_page->attr->sort = count ($this->tree);
			$this->tree[] = $new_page;
		}

		return true;
	}

	/**
	 * Remove a page from the tree. Removes all children as well
	 * unless you set $recursive to false.
	 */
	function remove ($id, $recursive = true) {
		$ref = $this->parent ($id);

		if ($ref) {
			foreach ($ref->children as $key => $child) {
				if ($child->attr->id == $id) {
					if (! $recursive) {
						// move all children to parent
						if (isset ($child->children)) {
							foreach ($child->children as $item) {
								$this->add ($item, $ref->attr->id);
							}
						}
					}

					unset ($ref->children[$key]);
					if (count ($ref->children) == 0) {
						unset ($ref->children);
						unset ($ref->state);
					}
					return true;
				}
			}
		} else {
			foreach ($this->tree as $key => $child) {
				if ($child->attr->id == $id) {
					if (! $recursive) {
						// move all children to root
						if (isset ($child->children)) {
							foreach ($child->children as $item) {
								$this->add ($item);
							}
						}
					}
					
					unset ($this->tree[$key]);
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Remove a specific path from the tree recursively. Used
	 * primarily by move().
	 */
	function remove_path ($path) {
		$id = $path[count ($path) - 1];
		if (! $id) {
			return false;
		}

		if (count ($path) > 1) {
			$ref = $this->node ($path[count ($path) - 2]);
		}

		if ($ref) {
			foreach ($ref->children as $key => $child) {
				if ($child->attr->id == $id) {
					unset ($ref->children[$key]);
					if (count ($ref->children) == 0) {
						unset ($ref->children);
						unset ($ref->state);
					}
					return true;
				}
			}
		} else {
			foreach ($this->tree as $key => $child) {
				if ($child->attr->id == $id) {
					unset ($this->tree[$key]);
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Move a page to the specified location ($ref) in the tree.
	 * $pos can be one of: after, before, inside (default is
	 * inside).
	 */
	function move ($id, $ref, $pos = 'inside') {
		$old_path = $this->path ($id);
		$ref_parent = $this->parent ($ref);
		$node = $this->node ($id);
		switch ($pos) {
			case 'before':
				$this->remove ($id);

				// add sorted
				if ($ref_parent) {
					$old_children = $ref_parent->children;
					$new_children = array ();
					$sort = 0;
					foreach ($old_children as $child) {
						if ($child->attr->id == $ref) {
							$node->attr->sort = $sort;
							$new_children[] = $node;
							$sort++;
						}
						$child->attr->sort = $sort;
						$new_children[] = $child;
						$sort++;
					}
					$ref_parent->children = $new_children;
				} else {
					$old_children = $this->tree;
					$new_children = array ();
					$sort = 0;
					foreach ($old_children as $child) {
						if ($child->attr->id == $ref) {
							$node->attr->sort = $sort;
							$new_children[] = $node;
							$sort++;
						}
						$child->attr->sort = $sort;
						$new_children[] = $child;
						$sort++;
					}
					$this->tree = $new_children;
				}

				break;
			case 'after':
				$this->remove ($id);

				// add sorted
				if ($ref_parent) {
					$old_children = $ref_parent->children;
					$new_children = array ();
					$sort = 0;
					foreach ($old_children as $child) {
						$child->attr->sort = $sort;
						$new_children[] = $child;
						$sort++;
						if ($child->attr->id == $ref) {
							$node->attr->sort = $sort;
							$new_children[] = $node;
							$sort++;
						}
					}
					$ref_parent->children = $new_children;
				} else {
					$old_children = $this->tree;
					$new_children = array ();
					$sort = 0;
					foreach ($old_children as $child) {
						$child->attr->sort = $sort;
						$new_children[] = $child;
						$sort++;
						if ($child->attr->id == $ref) {
							$node->attr->sort = $sort;
							$new_children[] = $node;
							$sort++;
						}
					}
					$this->tree = $new_children;
				}

				break;
			case 'inside':
				$this->remove ($id);
				$this->add ($node, $ref);
				break;
		}
		return true;
	}

	/**
	 * Save the tree out to the file.
	 */
	function save () {
		if (! file_put_contents ($this->file, json_encode ($this->tree))) {
			$this->error = 'Failed to save file: ' . $this->file;
			return false;
		}
		return true;
	}
}

?>
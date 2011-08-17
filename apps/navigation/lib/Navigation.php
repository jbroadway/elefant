<?php

/**
 * Handles managing the navigation tree as a json object.
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
	function find_ref ($id, $tree = false) {
		if (! $tree) {
			$tree = $this->tree;
		}
		
		foreach ($tree as $item) {
			if ($item->attr->id == $id) {
				return $item;
			}
			if (isset ($item->children)) {
				$res = $this->find_ref ($id, $item->children);
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
	function find_parent ($id, $tree = false) {
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
				$parent = $this->find_parent ($id, $item->children);
			}
		}
		return $parent;
	}

	/**
	 * Find the path to a specific node in the tree, including the node itself.
	 */
	function find_path ($id, $tree = false) {
		if (! $tree) {
			$tree = $this->tree;
		}

		foreach ($tree as $item) {
			if ($item->attr->id == $id) {
				return array ($id);
			} elseif (isset ($item->children)) {
				$res = $this->find_path ($id, $item->children);
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
			$ref = $this->find_ref ($parent);
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
		$ref = $this->find_parent ($id);

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
			$ref = $this->find_ref ($path[count ($path) - 2]);
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
		$old_path = $this->find_path ($id);
		$ref_path = $this->find_ref ($ref);
		$node = $this->find_ref ($id);
		switch ($pos) {
			case 'before':
				$this->remove ($old_path);
				// add to new_path before item
				break;
			case 'after':
				$this->remove ($old_path);
				// add to new_path after item
				break;
			case 'inside':
				$this->add ($node, $ref);
				$this->remove_path ($old_path);
				break;
		}
	}

	/**
	 * Save the tree out to the file.
	 */
	function save_tree () {
		if (! file_put_contents ($this->file, json_encode ($this->tree))) {
			$this->error = 'Failed to save file: ' . $this->file;
			return false;
		}
		return true;
	}
}

?>
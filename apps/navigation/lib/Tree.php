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
 * Handles managing a tree of objects as a JSON file. Items in the tree have the
 * following structure:
 *
 *     {"data":"Title or name","attr":{"id":"ID value","sort":0}}
 *
 * Item that have children will have an additional `children`
 * property that is an array of other items.
 *
 * Usage:
 *
 *     $n = new Tree ('conf/categories.json');
 *     // $n->tree contains data from conf/categories.json
 *
 *     $n->add ('Topics');
 *     $n->add ('Design', 'Topics');
 *     $n->add ('Web Design', 'Design');
 *     $n->add ('Print Design', 'Design');
 *     // etc.
 *
 *     $node = $n->node ('Web Design');
 *     // {"data":"Web Design","attr":{"id":"Web Design","sort":0}}
 *
 *     $path = $n->path ('Web Design');
 *     // array ('Topics', 'Design', 'Web Design')
 *
 *     $n->move ('Web Design', 'Design', 'before');
 *     $ids = $n->get_all_ids ();
 *     // array ('Topics', 'Web Design', 'Design', 'Print Design')
 *
 *     $n->remove ('Web Design');
 *
 *     // save out to conf/categories.json
 *     $n->save ();
 */
class Tree {
	/**
	 * The file that contained the JSON tree structure.
	 */
	public $file = null;

	/**
	 * The tree structure.
	 */
	public $tree = null;

	/**
	 * The error message if an error occurs, or false if no errors.
	 */
	public $error = false;

	/**
	 * Constructor method. Decodes the tree from the specified file in JSON format.
	 */
	public function __construct ($file) {
        $this->file = $file;
		$this->tree = json_decode (file_exists ($file) ? file_get_contents ($file) : '[]');
		if (json_last_error () != JSON_ERROR_NONE || ! is_array ($this->tree)) {
			$this->tree = [];
		}
	}

	/**
	 * Fetches the ->attr->id value for an item in the tree, verifying that the
	 * array keys exist to keep php 8 happy.
	 */
	public static function attr_id ($item) {
		if (! isset ($item->attr)) return '';
		if (! isset ($item->attr->id)) return '';
		return $item->attr->id;
	}

	/**
	 * Fetches the ->attr->classname value for an item in the tree, verifying that the
	 * array keys exist to keep php 8 happy.
	 */
	public static function attr_classname ($item) {
		if (! isset ($item->attr)) return '';
		if (! isset ($item->attr->classname)) return '';
		return $item->attr->classname;
	}

	/**
	 * Fetches the ->attr->classname value for an item in the tree, verifying that the
	 * array keys exist to keep php 8 happy.
	 */
	public static function attr_sort ($item) {
		if (! isset ($item->attr)) return '';
		if (! isset ($item->attr->sort)) return '';
		return $item->attr->sort;
	}

	/**
	 * Get all page ids from the tree.
	 */
	public function get_all_ids ($tree = false) {
		if (! $tree) {
			$tree = $this->tree;
		}

		$ids = array ();
		foreach ($tree as $item) {
			$ids[] = self::attr_id ($item);
			if (isset ($item->children)) {
				$ids = array_merge ($ids, $this->get_all_ids ($item->children));
			}
		}
		return array_unique ($ids);
	}

	/**
	 * Get the section nodes, meaning all nodes that have sub-nodes.
	 */
	public function sections ($tree = false) {
		if (! $tree) {
			$tree = $this->tree;
		}

		$sections = array ();
		foreach ($tree as $item) {
			if (isset ($item->children)) {
				$sections[] = self::attr_id ($item);
				$sections = array_merge ($sections, $this->sections ($item->children));
			}
		}
		return $sections;
	}

	/**
	 * Find a specific node in the tree.
	 */
	public function node ($id, $tree = false) {
		if (! $tree) {
			$tree = $this->tree;
		}
		
		foreach ($tree as $item) {
			if (self::attr_id ($item) == $id) {
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
	public function parent ($id, $tree = false) {
		if (! $tree) {
			$tree = $this->tree;
		}

		$parent = null;		
		foreach ($tree as $item) {
			if (self::attr_id ($item) == $id) {
				// found item itself, should have found parent by now
				return null;
			}
			if (isset ($item->children)) {
				foreach ($item->children as $child) {
					if (self::attr_id ($child) == $id) {
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
	 * If titles is true, it will return an associative array with the keys being
	 * object IDs and the values being their data attribute (usually, the title or
	 * name). If `$titles` is false, it will return an array of IDs only.
	 */
	public function path ($id, $titles = false, $tree = false) {
		if (! $tree) {
			if (is_array ($titles)) {
				$tree = $titles;
				$titles = false;
			} else {
				$tree = $this->tree;
			}
		}

		foreach ($tree as $item) {
			$_id = self::attr_id ($item);
			if ($_id == $id) {
				return $titles ? array ($id => $item->data ?? '') : [$id];
			} elseif (isset ($item->children)) {
				$res = $this->path ($id, $titles, $item->children);
				if ($res) {
					return $titles ? array_merge (array ($_id => $item->data ?? ''), $res) : array_merge ([$_id], $res);
				}
			}
		}

		return null;
	}

	/**
	 * Add an object to the tree under the specified parent node.
	 * `$id` can be an ID or a node object. If `$data` is passed, it
	 * it will set the `data` property to that instead of the value
	 * of `$obj`, which would typically be used as follows:
	 *
	 *     $mytree->add ('id-value', 'parent-id', 'Title here');
	 */
	public function add ($obj, $parent = false, $data = false) {
		if (! is_object ($obj)) {
			$data = $data ? $data : $obj;
			$obj = (object) [
				'data' => $data,
				'attr' => (object) [
					'id' => $obj,
					'sort' => 0
				]
			];
		}

		// locate $parent and add child
		if ($parent && ($ref = $this->node ($parent))) {
			if (! isset ($ref->children)) {
				$ref->children = [];
			}
			$obj->attr->sort = count ($ref->children);
			$ref->children[] = $obj;
			$ref->state = 'open';
		} else {
			$obj->attr->sort = count ($this->tree);
			$this->tree[] = $obj;
		}

		return true;
	}

	/**
	 * Remove an object from the tree. Removes all children as well
	 * unless you set $recursive to false.
	 */
	function remove ($id, $recursive = true) {
		$ref = $this->parent ($id);

		if ($ref) {
			foreach ($ref->children as $key => $child) {
				if (self::attr_id ($child) == $id) {
					if (! $recursive) {
						// move all children to parent
						if (isset ($child->children)) {
							foreach ($child->children as $item) {
								$this->add ($item, self::attr_id ($ref));
							}
						}
					}

					unset ($ref->children[$key]);
					if (count ($ref->children) == 0) {
						unset ($ref->children);
						unset ($ref->state);
					} else {
						// prevent array from becoming associative on save
						$ref->children = array_values ($ref->children);
					}
					return true;
				}
			}
		} else {
			foreach ($this->tree as $key => $child) {
				if (self::attr_id ($child) == $id) {
					if (! $recursive) {
						// move all children to root
						if (isset ($child->children)) {
							foreach ($child->children as $item) {
								$this->add ($item);
							}
						}
					}
					
					unset ($this->tree[$key]);
					// prevent array from becoming associative on save
					$this->tree = array_values ($this->tree);
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
	public function remove_path ($path) {
		$id = $path[count ($path) - 1];
		if (! $id) {
			return false;
		}

		if (count ($path) > 1) {
			$ref = $this->node ($path[count ($path) - 2]);
		}

		if ($ref) {
			if (isset ($ref->children)) {
				foreach ($ref->children as $key => $child) {
					if (self::attr_id ($child) == $id) {
						unset ($ref->children[$key]);
						if (count ($ref->children) == 0) {
							unset ($ref->children);
							if (isset ($ref->state)) {
								unset ($ref->state);
							}
						}
						return true;
					}
				}
			}
		} else {
			foreach ($this->tree as $key => $child) {
				if (self::attr_id ($child) == $id) {
					unset ($this->tree[$key]);
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Move an object to the specified location ($ref) in the tree.
	 * $pos can be one of: after, before, inside (default is
	 * inside).
	 */
	public function move ($id, $ref, $pos = 'inside') {
		$old_path = $this->path ($id);
		$ref_parent = $this->parent ($ref);
		$node = $this->node ($id);
		switch ($pos) {
			case 'before':
				$this->remove ($id);

				// add sorted
				if ($ref_parent) {
					$old_children = $ref_parent->children ?? [];
					$new_children = array ();
					$sort = 0;
					foreach ($old_children as $child) {
						if (self::attr_id ($child) == $ref) {
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
						if (self::attr_id ($child) == $ref) {
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
					$old_children = $ref_parent->children ?? [];
					$new_children = array ();
					$sort = 0;
					foreach ($old_children as $child) {
						$child->attr->sort = $sort;
						$new_children[] = $child;
						$sort++;
						if (self::attr_id ($child) == $ref) {
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
						if (self::attr_id ($child) == $ref) {
							$node->attr->sort = $sort;
							$new_children[] = $node;
							$sort++;
						}
					}
					$this->tree = $new_children;
				}

				break;
			case 'inside':
			case 'last':
				$this->remove ($id);
				$this->add ($node, $ref);
				break;
		}
		return true;
	}
	
	/**
	 * Update the tree from Json to the file.
	 */
	public function update ($tree) {
		$this->tree = $tree;
		return true;
	}
	

	/**
	 * Save the tree out to the file.
	 */
	public function save () {
		if (! file_put_contents ($this->file, json_encode ($this->tree))) {
			$this->error = 'Failed to save file: ' . $this->file;
			return false;
		}
		return true;
	}
}

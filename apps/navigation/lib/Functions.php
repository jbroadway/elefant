<?php

/**
 * Print the site tree as an HTML list with the current
 * context open as sub-lists up to the first-level children
 * of the current page. The currently active page will have
 * the class `current` and parents will have the class
 * `parent`.
 *
 * Usage:
 *
 *     $n = new Navigation;
 *     $path = $n->path ($page->id);
 *     $path = ($path) ? $path : array ();
 *     navigation_print_context ($n->tree, $path);
 */
function navigation_print_context ($tree, $path) {
	echo '<ul>';
	foreach ($tree as $item) {
		$_id = Tree::attr_id ($item);
		$_count = count ($path) - 1;
		if (isset ($path[$_count]) && $_id == $path[$_count]) {
			echo '<li class="current">' . Link::make ($_id, $item->data ?? '');
			if (isset ($item->children)) {
				if (is_object ($item->children)) {
					$item->children = (array) $item->children;
				}
				navigation_print_context ($item->children, $path);
			}
			echo '</li>';
		} elseif (in_array ($_id, $path)) {
			echo '<li class="parent">' . Link::make ($_id, $item->data ?? '');
			if (isset ($item->children)) {
				if (is_object ($item->children)) {
					$item->children = (array) $item->children;
				}
				navigation_print_context ($item->children, $path);
			}
			echo '</li>';
		} else {
			printf ('<li><a href="/%s">%s</a></li>', $_id, $item->data ?? '');
		}
	}
	echo '</ul>';
}

/**
 * Print the full site tree as an HTML list.
 */
function navigation_print_level ($tree) {
	echo '<ul>';
	foreach ($tree as $item) {
		echo '<li>' . Link::make (Tree::attr_id ($item), $item->data ?? '');
		if (isset ($item->children)) {
			if (is_object ($item->children)) {
				$item->children = (array) $item->children;
			}
			navigation_print_level ($item->children);
		}
		echo '</li>';
	}
	echo '</ul>';
}

/**
 * Prints the site tree for the navigation/dropmenu handler.
 */
function navigation_print_dropmenu ($tree, $id = false) {
	if ($id) {
		echo '<ul id="' . $id . '" class="dropmenu">';
	} else {
		echo '<ul class="dropmenu">';
	}

	foreach ($tree as $item) {
		$_id = Tree::attr_id ($item);
		$current = ($_id == Link::current()) ? ' class="current"' : '';
		if (empty ($current)) {
			$current = in_array ($_id, Link::active()) ? ' class="active"' : $current;
		}
		echo '<li' . $current . '>' . Link::make ($_id, $item->data ?? '');
		if (isset ($item->children)) {
			if (is_object ($item->children)) {
				$item->children = (array) $item->children;
			}
			navigation_print_level ($item->children);
		}
		echo '</li>';
	}
	echo '</ul>';
}

/**
 * Clears all cache keys for the navigation app.
 */
function navigation_clear_cache () {
	global $cache;
	$cache->delete ('_navigation_top');
	$cache->delete ('_c_navigation_map');
	$cache->delete ('_c_navigation_dropmenu');
}

/**
 * Returns a list of pages that are not in the navigation.
 */
function navigation_get_other_pages ($ids) {
	$pages = array ();
	$res = DB::fetch ("select id, title, menu_title, access from #prefix#webpage where access != 'private'");

	//Adds apps to Navigation, the new way
	$nav = Appconf::options ('nav');
	foreach ($nav as $id => $title) {
		$appObj = [
			'id' => $id,
			'title' => $title
		];
		$res[] = $appObj;
	}

	// Add apps to Navigation, the old way	
	$apps = glob ('apps/*');
	foreach ($apps as $app) {
		$app = str_replace ('apps/', '', $app);
		$ini = Appconf::get ($app);
		foreach ($ini as $section) {
			if (array_key_exists ('include_in_nav', $section) && $section['include_in_nav']
					&& array_key_exists ('title', $section) && $section['title'] != '') {
				$appObj = [];
				if (! in_array ($section['include_in_nav'], array ('1', 1, true), true)) {
					$appObj['id'] = ltrim ($section['include_in_nav'], '/');
				} else {
					$appPath = explode ('/',$app);
					$appObj['id'] = $appPath[0];
				}
				$appObj['title'] = $section['title'];
				$appObj['menu_title'] = array_key_exists ('menu_title', $section) ? $section['menu_title'] : $section['title'];
				$res[] = $appObj;
				break;
			}
		}
	}
   	
	foreach ($res as $p) {
		$p = is_object ($p) ? get_object_vars ($p) : $p;
		if (isset ($p['id']) && in_array ($p['id'], $ids)) {
			// skip if in tree
			continue;
		}
		if (isset ($p['menu_title']) && ! empty ($p['menu_title'])) {
			$pages[$p['id']] = $p['menu_title'];
		} else {
			$pages[$p['id']] = $p['title'] ?? '';
		}
	}

	uasort ($pages, function ($a, $b) {
		if ($a === $b) {
			return 0;
		}
		
		return ($a < $b) ? -1 : 1;
	});

	return $pages;
}

function navigation_print_admin_tree ($tree, $tree_root=true) {
	echo ($tree_root) ?'<ul class="tdd-tree">' : "<ul>";
	foreach ($tree as $item) {
		$_id = Tree::attr_id ($item);
		printf ('<li id="%s"><i class="%s"></i> %s <span>/%s</span>', $_id, Tree::attr_classname ($item),  $item->data ?? '', $_id);
		if (isset ($item->children)) {
			if (is_object ($item->children)) {
				$item->children = (array) $item->children;
			}
			navigation_print_admin_tree ($item->children, false);
		}
		echo '</li>';
	}
	echo '</ul>';
}

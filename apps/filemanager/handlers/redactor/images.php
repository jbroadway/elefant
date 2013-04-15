<?php

/**
 * List available images for the wysiwyg editor.
 */

$page->layout = false;

$this->require_admin ();

$images = array ();

$root = conf('Paths','filemanager_path');

$glob = array (
	glob ($root . '/*.{png,jpg,gif,jpeg}', GLOB_BRACE),
	glob ($root . '/*/*.{png,jpg,gif,jpeg}', GLOB_BRACE),
	glob ($root . '/*/*/*.{png,jpg,gif,jpeg}', GLOB_BRACE),
	glob ($root . '/*/*/*/*.{png,jpg,gif,jpeg}', GLOB_BRACE)
);

foreach ($glob as $list) {
	if (is_array ($list)) {
		foreach ($list as $item) {
			$images[] = array (
				'thumb' => '/' . $item,
				'image' => '/' . $item,
				'folder' => str_replace ('/', ' / ', dirname ($item))
			);
		}
	}
}

echo stripslashes (json_encode ($images));

?>

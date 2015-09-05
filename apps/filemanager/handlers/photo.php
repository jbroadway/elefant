<?php

/**
 * Creates a space where a photo can be updated by a site admin
 * and will be automatically resized to fit the dimensions set
 * out in advance.
 *
 * Usage:
 *
 *     {! filemanager/photo?key=about-photo&width=150&height=200 !}
 *
 * Options:
 *
 * - `key`     - A unique ID for the photo spot (alphanumeric, no spaces).
 * - `width`   - The width of the photo spot (default = 300).
 * - `height`  - The height of the photo spot (default = 200).
 * - `alt`     - Text for the alt attribute.
 * - `class`   - Class name(s) for the class attribute.
 * - `default` - The path to a default photo, otherwise a placehold.it image is used.
 */

if (! $this->internal) {
	// GET request means return JSON for updated photo
	$this->require_acl ('admin', 'filemanager');
	$page->layout = false;
	$data = $_GET;
}

$data['width'] = (isset ($data['width']) && is_numeric ($data['width']))
	? $data['width']
	: 300;

$data['height'] = (isset ($data['height']) && is_numeric ($data['height']))
	? $data['height']
	: 200;

$data['alt'] = isset ($data['alt']) ? $data['alt'] : '';
$data['class'] = isset ($data['class']) ? $data['class'] : false;

if (! isset ($data['key'])) {
	echo '<!-- Error: Missing key value -->';
	return;
}

if (! $this->internal) {
	if (isset ($data['photo'])) {
		if (! preg_match ('|^/files/|', $data['photo'])) {
			$data['photo'] = null;
		} else {
			$photo = preg_replace ('|^/files/|', '', $data['photo']);
			if (! FileManager::verify_file ($photo)) {
				$data['photo'] = null;
			}
		}
	} else {
		$data['photo'] = null;
	}
}

$data['photo'] = Image::for_key ($data['key'], $data['photo']);

if ((! isset ($data['link']) || ! $data['link'] || $data['link'] === '') && $data['photo'] !== null) {
	$photo = preg_replace ('|^/files/|', '', $data['photo']);
	$data['link'] = FileManager::prop ($photo, 'link');
}

if ($data['photo']) {
	$data['src'] = '/' . Image::resize ($data['photo'], $data['width'], $data['height'], 'cover', 'ext');
} elseif (isset ($data['default'])) {
	if (preg_match ('/^https?:\/\//', $data['default'])) {
		$data['src'] = $data['default'];
	} else {
		$data['src'] = '/' . Image::resize (ltrim ($data['default'], '/'), $data['width'], $data['height'], 'cover', 'ext');
	}
} else {
	$data['src'] = 'http://placehold.it/' . $data['width'] . 'x' . $data['height'];
}

if (! $this->internal) {
	header ('Content-Type: application/json');
	echo json_encode (array ('success' => true, 'data' => $data));
	return;
}

if (User::is_valid () && User::require_acl ('admin', 'filemanager')) {
	echo $this->run ('filemanager/util/browser');
	$page->add_script ('/apps/filemanager/js/jquery.photoswitcher.js');
}

echo $tpl->render ('filemanager/photo', $data);

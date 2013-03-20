<?php

/**
 * Save the changes from Aviary for an image.
 */

$this->require_admin ();
$page->layout = false;
header ('Content-Type: application/json');

if (! isset ($_GET['file'])) {
	echo json_encode (array (
		'success' => false,
		'error' => __ ('No file specified.')
	));
	return;
}

if (! FileManager::verify_file ($_GET['file'])) {
	echo json_encode (array (
		'success' => false,
		'error' => __ ('Invalid file.')
	));
	return;
}

if (! isset ($_GET['url'])) {
	echo json_encode (array (
		'success' => false,
		'error' => __ ('No image url specified.')
	));
	return;
}

$res = fetch_url ($_GET['url']);
if (! $res) {
	echo json_encode (array (
		'success' => false,
		'error' => __ ('Updated image not found.')
	));
	return;
}

if (! file_put_contents ('files/' . $_GET['file'], $res)) {
	echo json_encode (array (
		'success' => false,
		'error' => __ ('Unable to write to the file. Please check your folder permissions and try again.')
	));
	return;
}

echo json_encode (array (
	'success' => true,
	'data' => __ ('File saved.')
));

?>
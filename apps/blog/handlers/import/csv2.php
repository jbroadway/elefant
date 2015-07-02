<?php

/**
 * Finishes importing a CSV file.
 */

$this->require_admin ();

$page->layout = 'admin';
$page->title = __ ('CSV Importer');

$imported = 0;

$file = 'cache/blog_csv_import.csv';

if (! file_exists ($file)) {
	echo '<p>' . __ ('Uploaded CSV file not found.') . '</p>';
	echo '<p><a href="/blog/import">' . __ ('Back') . '</a></p>';
	return;
}

set_time_limit (0);
ini_set ('auto_detect_line_endings', true);

$res = array ();
if (($f = fopen ($file, 'r')) !== false) {
	while (($row = fgetcsv ($f, 0, ',')) !== false) {
		if (count ($row) === 1 && $row[0] === null) {
			// ignore blank lines, which come through as array(null)
			continue;
		}
		$res[] = $row;
	}
	fclose ($f);
} else {
	echo '<p>' . __ ('Unable to parse the uploaded file.') . '</p>';
	echo '<p><a href="/blog/import">' . __ ('Back') . '</a></p>';
	return;
}

// Map fields
$title = false;
$author = false;
$date = false;
$content = false;
$tags = false;

foreach ($_POST as $k => $v) {
	if (strpos ($k, 'map-') === 0 && $v !== '') {
		$n = (int) str_replace ('map-', '', $k);
		${$v} = $n;
	}
}

// Remove first line
array_shift ($res);

foreach ($res as $row) {
	$post = array (
		'title' => ($title !== false) ? $row[$title] : '',
		'author' => ($author !== false) ? $row[$author] : '',
		'ts' => ($date !== false) ? gmdate ('Y-m-d H:i:s', strtotime ($row[$date])) : gmdate ('Y-m-d H:i:s'),
		'published' => $_POST['published'],
		'body' => ($content !== false) ? $row[$content] : '',
		'tags' => ($tags !== false) ? $row[$tags] : ''
	);

	$p = new blog\Post ($post);
	if ($p->put ()) {
		Versions::add ($p);
		$imported++;
	}
}

echo '<p>' . __ ('Imported %d posts.', $imported) . '</p>';
echo '<p><a href="/blog/admin">' . __ ('Continue') . '</a></p>';

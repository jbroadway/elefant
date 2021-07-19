<?php

/**
 * Implements a blog post importer from a CSV file.
 */

$this->require_admin ();

$page->layout = 'admin';
$page->title = __ ('CSV Importer');

$f = new Form ('post');

if ($f->submit ()) {
	if (move_uploaded_file ($_FILES['import_file']['tmp_name'], 'cache/blog_csv_import.csv')) {
		$file = 'cache/blog_csv_import.csv';

		set_time_limit (0);
		ini_set ('auto_detect_line_endings', true);

		$headers = array ();
		$samples = array ();
		if (($f = fopen ($file, 'r')) !== false) {
			while (($row = fgetcsv ($f, 0, ',')) !== false) {
				if (count ($row) === 1 && $row[0] === null) {
					// ignore blank lines, which come through as array(null)
					continue;
				}
				if (count ($headers) === 0) {
					$headers = $row;
				} elseif (count ($samples) < 3) {
					$samples[] = $row;
				} else {
					break;
				}
			}
			fclose ($f);

			require_once ('apps/blog/lib/Filters.php');

			echo $tpl->render ('blog/import/csv2', array (
				'headers' => $headers,
				'samples' => $samples
			));

			return;
		} else {
			echo '<p><strong>' . __ ('Unable to parse the uploaded file.') . '</strong></p>';
	
			if (file_exists ($file)) {
				unlink ($file);
			}
		}
	} else {
		echo '<p><strong>' . __ ('Error uploading file.') . '</strong></p>';
	}
}

$o = new StdClass;

echo $tpl->render ('blog/import/csv', $o);

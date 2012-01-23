<?php

/**
 * Implements a blog post importer from a CSV file.
 */

$this->require_admin ();

$page->layout = 'admin';
$page->title = i18n_get ('CSV Importer');

$f = new Form ('post');

if ($f->submit ()) {
	if (move_uploaded_file ($_FILES['import_file']['tmp_name'], 'cache/blog_csv_import.csv')) {
		$file = 'cache/blog_csv_import.csv';

		$res = blog\CsvParser::parse ($file);

		if (is_array ($res)) {
			$headers = array_shift ($res);
			$samples = array ();
			for ($i = 0; $i < 3; $i++) {
				if (isset ($res[$i])) {
					$samples[] = $res[$i];
				}
			}

			require_once ('apps/blog/lib/Filters.php');

			echo $tpl->render ('blog/import/csv2', array (
				'headers' => $headers,
				'samples' => $samples
			));
			return;
		} else {
			echo '<p><strong>' . i18n_get ('Unable to parse the uploaded file.') . '</strong></p>';
		}
	} else {
		echo '<p><strong>' . i18n_get ('Error uploading file.') . '</strong></p>';
	}
}

$o = new StdClass;

echo $tpl->render ('blog/import/csv', $o);

?>
<?php

$this->require_admin ();

$page->layout = false;

$index = unserialize (file_get_contents ('lang/_index.php'));
if (! isset ($index[$_GET['string']])) {
	printf ('<p>%s</p>', __ ('String not found.'));
	return;
}

$string = $index[$_GET['string']];
$string['src'] = is_array ($string['src']) ? $string['src'] : array ($string['src']);

$contexts = array ();

foreach ($string['src'] as $source) {
	$lines = file ($source);
	foreach ($lines as $line => $text) {
		if (strpos ($text, $_GET['string']) !== false) {
			$start = $line >= 2 ? $line - 2 : 0;

			$slice = array_slice ($lines, $start, 5);
			$code = '';
			foreach ($slice as $n => $single) {
				$code .= '<span class="line-number">' . ($start + $n + 1) . '.</span> ' . Template::sanitize ($single);
			}
			$code = str_replace ($_GET['string'], '<span class="trans-text">' . $_GET['string'] . '</span>', $code);

			$contexts[] = (object) array (
				'file' => $source,
				'code' => $code
			);
		}
	}
}

echo View::render ('translator/sources', array ('contexts' => $contexts));

?>
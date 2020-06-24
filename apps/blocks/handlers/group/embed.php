<?php

/**
 * Wrapper for embedding `blocks/group` blocks into content via Dynamic Objects.
 */

$opts = [
	'level' => $data['level'],
	'divs' => 'on'
];

// 100 units means multi-row at 100% each
if ($data['units'] == '100') {
	$opts['rows'] = 'on';
} else {
	$opts['units'] = $data['units'];
	echo '<div class="e-row-variable">' . PHP_EOL;
}

// If wildcard contains a list of IDs, set the ID list instead
if (strpos ($data['wildcard'], ',') !== false) {
	$data['wildcard'] = trim (preg_replace ('/\s?,\s?/', ',', $data['wildcard']));
	$opts['id'] = explode (',', $data['wildcard']);
} else {
	$opts['wildcard'] = trim ($data['wildcard']);
}

echo $this->run ('blocks/group', $opts);

if ($data['units'] != '100') {
	echo '</div>' . PHP_EOL;
}

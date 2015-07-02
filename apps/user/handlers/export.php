<?php

/**
 * Export users as CSV.
 */

$this->require_acl ('admin', 'user');

$page->layout = false;
header ('Cache-control: private');
header ('Content-Type: text/plain');
header ('Content-Disposition: attachment; filename=accounts-' . gmdate ('Y-m-d') . '.csv');

$res = DB::fetch (
	'select
		if(
			locate(" ", name) > 0,
			substring(name, 1, locate(" ", name) - 1),
			name
		) as first_name,
		if(
			locate(" ", name) > 0,
			substring(name, locate(" ", name) + 1),
			null
		) as last_name,
		email, phone, address, address2 as address_2, city, state,
		country, zip, title, company, website, about
	 from
	 	#prefix#user
	 order by last_name asc, first_name asc'
);

if (! is_array ($res)) {
	return;
}

if (count ($res) > 0) {
	$keys = array_keys ((array) $res[0]);
	$keys = array_map ('user\Filter::csv_header', $keys);
	echo join (',', $keys) . "\n";
}

foreach ($res as $row) {
	$sep = '';
	foreach ((array) $row as $k => $v) {
		$v = str_replace ('"', '""', $v);
		if (strpos ($v, ',') !== false) {
			$v = '"' . $v . '"';
		}
		$v = str_replace (array ("\n", "\r"), array ('\\n', '\\r'), $v);
		echo $sep . $v;
		$sep = ',';
	}
	echo "\n";
}

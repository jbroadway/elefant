<?php

/**
 * Finishes importing a CSV file.
 */

$this->require_acl ('admin', 'user');

$page->layout = 'admin';
$page->title = __ ('CSV Importer');

$imported = 0;

$file = 'cache/user_csv_import.csv';

if (! file_exists ($file)) {
	echo '<p>' . __ ('Uploaded CSV file not found.') . '</p>';
	echo '<p><a href="/user/import">' . __ ('Back') . '</a></p>';
	return;
}

$res = blog\CsvParser::parse ($file);
if (! $res) {
	echo '<p>' . __ ('Unable to parse the uploaded file.') . '</p>';
	echo '<p><a href="/user/import">' . __ ('Back') . '</a></p>';
	return;
}

// Map fields
$name = false;
$first_name = false;
$last_name = false;
$email = false;
$company = false;
$title = false;
$website = false;
$photo = false;
$about = false;
$phone = false;
$address = false;
$address2 = false;
$city = false;
$state = false;
$country = false;
$zip = false;

$fields = ExtendedFields::for_class ('User');

foreach ($_POST as $k => $v) {
	if (strpos ($k, 'map-') === 0 && $v !== '') {
		$n = (int) str_replace ('map-', '', $k);
		${$v} = $n;
	}
}

// Remove first line
array_shift ($res);

foreach ($res as $k => $row) {
	$name_joined = ($first_name !== false) ? $row[$first_name] . ' ' . $row[$last_name] : '';
	$user = array (
		'name' => ($name !== false) ? $row[$name] : $name_joined,
		'email' => ($email !== false) ? $row[$email] : '',
		'company' => ($company !== false) ? $row[$company] : '',
		'title' => ($title !== false) ? $row[$title] : '',
		'website' => ($website !== false) ? $row[$website] : '',
		'photo' => ($photo !== false) ? $row[$photo] : '',
		'about' => ($about !== false) ? $row[$about] : '',
		'phone' => ($phone !== false) ? $row[$phone] : '',
		'address' => ($address !== false) ? $row[$address] : '',
		'address2' => ($address2 !== false) ? $row[$address2] : '',
		'city' => ($city !== false) ? $row[$city] : '',
		'state' => ($state !== false) ? $row[$state] : '',
		'country' => ($country !== false) ? $row[$country] : '',
		'zip' => ($zip !== false) ? $row[$zip] : '',
		'password' => '',
		'type' => 'member',
		'expires' => gmdate ('Y-m-d H:i:s'),
		'signed_up' => gmdate ('Y-m-d H:i:s'),
		'updated' => gmdate ('Y-m-d H:i:s')
	);
	
	if (! Validator::validate ($user['email'], 'unique', '#prefix#user.email')) {
		continue;
	}

	$u = new User ($user);

	$fields = ExtendedFields::for_class ('User');
	foreach ($fields as $field) {
		if (isset (${$field->name}) && ${$field->name} !== false) {
			$u->ext ($field->name, $row[${$field->name}]);
		}
	}

	if ($u->put ()) {
		Versions::add ($u);
		$imported++;
	}
}

echo '<p>' . __ ('Imported %d members.', $imported) . '</p>';
echo '<p><a href="/user/admin">' . __ ('Continue') . '</a></p>';

<?php

/**
 * Make the field headers for the admin view.
 */
function make_fields_header ($fields) {
	$out = '';
	$limit = count ($fields) > 3 ? 3 : count ($fields);
	if ($limit === 1) {
		$width = 84;
	} elseif ($limit === 2) {
		$width = 42;
	} else {
		$width = 28;
	}
	for ($i = 0; $i < $limit; $i++) {
		$out .= sprintf (
			"\t\t<th width=\"%d%%\">{\" %s \"}</th>\n",
			$width,
			ucfirst ($fields[$i])
		);
	}
	return $out;
}

/**
 * Make the field rows for the admin view.
 */
function make_fields_row ($fields) {
	$out = '';
	$limit = count ($fields) > 3 ? 3 : count ($fields);
	for ($i = 0; $i < $limit; $i++) {
		$out .= sprintf (
			"\t\t<td>{{ loop_value->%s }}</td>\n",
			$fields[$i]
		);
	}
	return $out;
}

?>
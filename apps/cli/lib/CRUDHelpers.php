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
			cli\Filter::label ($fields[$i]->name)
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
			$fields[$i]->name
		);
	}
	return $out;
}

/**
 * MySQL type from field type.
 */
function crud_mysql_type ($type) {
	switch ($type) {
		case 'pkey':
			return 'int not null auto_increment primary key';
		case 'date':
			return 'date not null';
		case 'datetime':
			return 'datetime not null';
		case 'time':
			return 'time not null';
		case 'textarea':
		case 'wysiwyg':
			return 'text not null';
		case 'select':
		case 'radio':
		case 'checkbox':
		case 'text':
		case 'email':
		case 'password':
		default:
			return 'char(48) not null';
	}
}

/**
 * PostgreSQL type from field type.
 */
function crud_pgsql_type ($type) {
	switch ($type) {
		case 'date':
			return 'date not null';
		case 'datetime':
			return 'datetime not null';
		case 'time':
			return 'time not null';
		case 'textarea':
		case 'wysiwyg':
			return 'text not null';
		case 'select':
		case 'radio':
		case 'checkbox':
		case 'text':
		case 'email':
		case 'password':
		default:
			return 'char(48) not null';
	}
}

/**
 * SQLite type from field type.
 */
function crud_sqlite_type ($type) {
	switch ($type) {
		case 'pkey':
			return 'integer primary key';
		case 'date':
			return 'date not null';
		case 'datetime':
			return 'datetime not null';
		case 'time':
			return 'time not null';
		case 'textarea':
		case 'wysiwyg':
			return 'text not null';
		case 'select':
		case 'radio':
		case 'checkbox':
		case 'text':
		case 'email':
		case 'password':
		default:
			return 'char(48) not null';
	}
}

<?php

/**
 * Rename associated blocks linked to wildcard blocks/group includes
 * in page layouts.
 */

if (! $this->internal) {
	die ('Must be called by another handler');
}

$old_id = $this->data['page'];
$new_id = $this->data['id'];

if ($old_id != $new_id) {
	DB::execute (
		'update #prefix#block set id = replace(id, ?, ?) where id like ?',
		$old_id . '-',
		$new_id . '-',
		$old_id . '-%'
	);
}

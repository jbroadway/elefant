<?php

/**
 * Extends a form with a "Custom Fields" section that
 * displays inputs for any ExtendedModel-based class.
 *
 * Usage:
 *
 * 1. Add this to your form view:
 *
 *     {! admin/util/extended?extends=blog\Post !}
 *
 * 2. Call this in the form handler function:
 *
 *     $post->update_extended ($_POST);
 *
 * 3. Create a link to edit the custom fields for a given
 * class somewhere in your app:
 *
 *     <a href="/admin/extended?extends=blog\Post">{"Custom fields"}</a>
 */

if (! $this->internal) {
	return;
}

$this->require_admin ();

if (! isset ($data['extends'])) {
	return;
}

$class = $data['extends'];
if (! class_exists ($class)) {
	return;
}

$data['fields'] = ExtendedFields::for_class ($class);

if ($data['fields'] || count ($data['fields']) === 0) {
	echo $tpl->render ('admin/util/extended', $data);
}

?>
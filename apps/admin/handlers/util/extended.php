<?php

/**
 * Extends a form with a "Custom Fields" section that
 * displays inputs for any ExtendedModel-based class.
 *
 * Usage:
 *
 * 1. Add this to your form view template:
 *
 *     {! admin/util/extended?extends=blog\Post !}
 *
 * For update forms, pass the extended field values as well:
 *
 *     {! admin/util/extended?extends=blog\Post&values=[extra|none] !}
 *
 * 2. For update forms, call this in the form handler function,
 * before calling `$post->put ()`:
 *
 *     $post->update_extended ();
 *
 * 3. Create a link to edit the custom fields for a given
 * class somewhere in your app:
 *
 *     <a href="/admin/extended?extends=blog\Post&name=Blog+Posts">{"Custom fields"}</a>
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

$load_assets = false;

if ($data['fields'] || count ($data['fields']) === 0) {
	foreach ($data['fields'] as $k => $field) {
		if (isset ($data['values']) && isset ($data['values'][$field->name])) {
			$data['fields'][$k]->value = $data['values'][$field->name];
		}

		if ($field->type === 'select') {
			$data['fields'][$k]->options = preg_split ("/[\r\n]+/", $field->options);
		} elseif ($field->type === 'file') {
			$load_assets = true;
		}
	}

	if ($load_assets) {
		$page->add_style ('/css/wysiwyg/jquery.wysiwyg.css');
		$page->add_style ('/css/files/wysiwyg.fileManager.css');
		$page->add_script ('/js/wysiwyg/jquery.wysiwyg.js');
		$page->add_script ('/js/wysiwyg/plugins/wysiwyg.fileManager.js');
	}

	echo $tpl->render ('admin/util/extended', $data);
}

?>
<?php

/**
 * Extends a form with a "Custom Fields" section that
 * displays inputs for any ExtendedModel-based class.
 *
 * Usage:
 *
 * 1. Add this to your form view template:
 *
 *     {! admin/util/extended?extends=blog\Post&name=Blog+Posts !}
 *
 * For update forms, pass the extended field values as well:
 *
 *     {! admin/util/extended?extends=blog\Post&name=Blog+Posts&values=[extra|none]&id=[id] !}
 *
 * 2. For update forms, call this in the form handler function,
 * before calling `$post->put ()`:
 *
 *     $post->update_extended ();
 *
 * 3. Create a link to edit the custom fields for a given
 * class somewhere in your app:
 *
 *     <a href="/admin/extended?extends=blog\Post&name=Blog+Posts">{"Custom Fields"}</a>
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
$data['modal'] = (isset ($data['modal']) && $data['modal'] !== 'false') ? true : false;
$data['open'] = false;
$data['id'] = isset ($data['id']) ? $data['id'] : false;

$load_assets = false;
$load_date_widget = false;

if ($data['fields'] || count ($data['fields']) === 0) {
	foreach ($data['fields'] as $k => $field) {
		if (isset ($data['values']) && isset ($data['values'][$field->name])) {
			$data['fields'][$k]->value = $data['values'][$field->name];
		}

		if ($field->type === 'select') {
			$data['fields'][$k]->options = preg_split ("/[\r\n]+/", $field->options);
		} elseif ($field->type === 'file' || $field->type === 'image') {
			$load_assets = true;
		} elseif ($field->type === 'date' || $field->type === 'datetime') {
			$load_date_widget = true;
		} elseif (strpos ($field->type, '_') !== false) {
			list ($app, $extra) = explode ('_', $field->type);
			$fields = parse_ini_file ('apps/'. $app . '/conf/fields.php', true);
			if (isset ($fields[$field->type])) {
				$settings = $fields[$field->type];
				if ($settings['type'] === 'select') {
					$data['fields'][$k]->type = 'select';
					$data['fields'][$k]->options = call_user_func (
						$settings['callback'],
						$class,
						$data['id']
					);
				}
			}
		}
		
		if ($data['fields'][$k]->required) {
			$data['open'] = true;
		}
	}

	if ($load_assets) {
		$this->run ('filemanager/util/browser');
	}
	
	if ($load_date_widget) {
		$this->run ('admin/util/datewidget');
	}

	echo $tpl->render ('admin/util/extended', $data);
}

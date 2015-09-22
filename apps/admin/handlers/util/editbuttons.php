<?php

/**
 * Use this handler to add edit buttons for any object in a page.
 * Note: This helper will output nothing unless the current user
 * has admin-level access.
 *
 * Usage:
 *
 *     {! admin/util/editbuttons
 *         ?id=[id]
 *         &class=myapp\Product
 *         &add=/myapp/add
 *         &edit=/myapp/edit
 *         &delete=/myapp/delete
 *         &versions=true
 *         &label=Product !}
 *
 * Parameters:
 *
 * - `id` -				Required, the ID or primary key of the object
 * - `class` -			Required, the class name of the object
 * - `label` -			The label or display name of the object, e.g., "Page"
 * - `add` -			To include an 'add' link, this must be the URL of the add link
 * - `edit` -			Include an 'edit' link, this must be the URL of the add link
 * - `delete` -			Include a 'delete' link, this must be the URL of the add link
 * - `versions` -		Include a 'versions' link, default = false
 *
 * If label is omitted, it will look for the value in the `$display_name`
 * static properties of the class itself (`$class::$display_name`). Failing
 * to find that, it will use the label "Item".
 *
 * The ID value will be passed to the edit handler as an `?id=` parameter in
 * a GET request. For delete, this will be an `id` parameter in a POST request.
 *
 * Automatically adds the appropriate access control checks on each action, and
 * hides the button if the current user doesn't have permission:
 *
 * - `add` -			`admin/edit` and `admin/add`
 * - `edit` - 			`admin/edit`
 * - `delete` - 		`admin/delete`
 * - `versions` - 		`admin/versions`
 */

if (! User::require_admin ()) {
	return;
}

if (! isset ($this->data['id'])) {
	echo '<!-- error in admin/util/editbuttons, missing parameter: id -->';
	return;
}

if (! isset ($this->data['class'])) {
	echo '<!-- error in admin/util/editbuttons, missing parameter: class -->';
	return;
}

$class = $this->data['class'];

$this->data['add'] = isset ($this->data['add']) ? $this->data['add'] : false;
$this->data['edit'] = isset ($this->data['edit']) ? $this->data['edit'] : false;
$this->data['delete'] = isset ($this->data['delete']) ? $this->data['delete'] : false;
$this->data['versions'] = isset ($this->data['versions']) ? $this->data['versions'] : false;

$this->data['label'] = isset ($this->data['label'])
	? $this->data['label']
	: (isset ($class::$display_name) ? $class::$display_name : __ ('Item'));

$this->data['confirm_msg'] = __ ('Are you sure you want to delete the current %s?', strtolower ($this->data['label']));

$lock = new Lock ($this->data['class'], $this->data['id']);
$this->data['locked'] = $lock->exists ();

echo $tpl->render ('admin/util/editbuttons', $this->data);

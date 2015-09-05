<?php

/**
 * Helper to make inline editable content. Supports single-line text,
 * paragraph text, and select boxes for input.
 *
 * Usage:
 *
 * ### 1. Load this handler either in your handler:
 *
 *     $this->run (
 *         'admin/util/editable',
 *         array (
 *             'url' => '/myapp/editable'
 *         )
 *     );
 *
 * Or anywhere in your view:
 *
 *     {! admin/util/editable?url=/myapp/editable !}
 *
 * ### 2. Mark your editable areas in your view template via:
 *
 *     <h2 class="editable-text" id="{{id}}">{{name}}</h2>
 *
 *     <p class="editable-textarea" id="{{id}}"
 *         data-property="description">{{description}}</h2>
 *
 *     <!-- select with options, note the single-quotes -->
 *     <p class="editable-select" id="{{id}}"
 *         data-options='{{options|json_encode}}'>{{value}}</p>
 *     
 *     <!-- add a delete button -->
 *     <h2 class="editable-text" id="2"
 *         data-property="title"
 *         data-url="/myapp/category/edit"
 *         data-delete="/myapp/category/delete">Category Title</h2>
 *
 * Valid classes for editable elements currently include:
 *
 * - `.editable-text`
 * - `.editable-textarea`
 * - `.editable-select`
 *
 * The plugin reads the following properties of the HTML element as the edit options:
 *
 * - `id` - The ID of the object to be updated
 * - `data-property` - The name of the property to be updated
 * - `data-url` - Overrides the default URL to POST updates to for this element
 * - `data-delete` - An alternate URL to POST deletes to (and includes a 'Delete' button)
 * - `data-options` - A JSON-encoded object containing the available select options
 *
 * `id` is the only required property. `data-options` is required for select options.
 *
 * ## Server-side handling
 *
 * The URL that handles saving the value will receive the following
 * parameters:
 *
 * - `id` - The ID of the object to be updated
 * - `property` - The name of the property to be updated (if set via `data-property`)
 * - `value` - The new property value
 * - `type` - The type of input (text, textarea, select)
 * - `label` - For select inputs, this is the label of the selected value
 *
 * The handler should respond with the escaped `value` parameter, except for
 * select inputs which should return the escaped `label` parameter. For example:
 *
 *     <?php
 *     
 *     $page->layout = false;
 *     
 *     $obj = myapp\MyModel ($_POST['id']);
 *     $obj->{$_POST['name']} = $_POST['value'];
 *     $obj->put ();
 *     
 *     if ($_POST['type'] === 'select') {
 *         echo Template::sanitize ($_POST['label']);
 *         return;
 *     }
 *     echo Template::sanitize ($_POST['value']);
 *
 * To send an error message, use the following code:
 *
 *     $this->add_notification (__ ('Unable to save changes.'));
 *     echo $this->error (500, 'Error message');
 *     return;
 *
 * To add a notification upon successful requests, you can also use the
 * [[Controller]]'s `add_notification()` method:
 *
 *     $this->add_notification (__ ('Changes saved.'));
 *     echo Template::sanitize ($_POST['value']);
 */

$this->run ('admin/util/i18n');
$page->add_style ('/apps/admin/css/editable.css');
$page->add_script ('/apps/admin/js/jquery.autogrow.min.js');
$page->add_script ('/apps/admin/js/jquery.jeditable.min.js');
$page->add_script ('/apps/admin/js/jquery.jeditable.autogrow.js');
$page->add_script ('/apps/admin/js/editable.js');
$page->add_script ('/apps/admin/js/jquery.jeditable.deletable.js');
if (isset ($data['url'])) {
	$page->add_script ('<script>var editable_default_url = \'' . $data['url'] . '\';</script>');
}
$page->add_script (I18n::export (
	'Saving...',
	'Cancel',
	'Save',
	'Delete',
	'Click to edit',
	'Unable to save changes.',
	'Are you sure you want to delete this item?'
));

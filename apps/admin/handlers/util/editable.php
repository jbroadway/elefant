<?php

/**
 * Helper to make things inline editable.
 *
 * Usage:
 *
 * 1. Load this handler either in your handler:
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
 * 2. Mark your editable areas in your view
 * template via:
 *
 *     <h2 class="editable-text" id="{{id}}">{{name}}</h2>
 *
 *     <p class="editable-textarea" id="description-{{id}}">{{description}}</h2>
 *
 *     <p class="editable-select" id="{{id}}"
 *         data-options='{{options|json_encode}}'>{{value}}</p>
 *
 * Valid classes for your editable elements include:
 *
 * - .editable-text
 * - .editable-textarea
 * - .editable-select
 *
 * The URL that handles saving the value will receive the following
 * parameters:
 *
 * - `id` - The field ID
 * - `value` - The new value of the field
 * - `type` - The type of input (text, textarea, select)
 * - `label` - For select inputs, the label of the selected value
 *
 * The handler should respond with the escaped `value` parameter,
 * except for select inputs which should return the escaped `label`
 * parameter. For example:
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
 *     ?>
 */

$page->add_style ('/apps/admin/css/editable.css');
$page->add_script ('/apps/admin/js/jquery.autogrow.min.js');
$page->add_script ('/apps/admin/js/jquery.jeditable.min.js');
$page->add_script ('/apps/admin/js/jquery.jeditable.autogrow.js');
$page->add_script ('/apps/admin/js/editable.js');
if (isset ($data['url'])) {
	$page->add_script ('<script>var editable_default_url = \'' . $data['url'] . '\';</script>');
}
$page->add_script (I18n::export (
	'Saving...',
	'Cancel',
	'OK',
	'Click to edit'
));

?>
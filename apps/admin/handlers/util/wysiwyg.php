<?php

/**
 * Call this to turn a textarea into a wysiwyg editor.
 *
 * In PHP code, call it like this:
 *
 *     $this->run ('admin/util/wysiwyg', array ('field_id' => 'body'));
 *
 * In a view template, call it like this:
 *
 *     {! admin/util/wysiwyg?field_id=my_field !}
 */

$page->add_style ('/js/jquery-ui/jquery-ui.css');
if (detect ('msie 7')) {
	$page->add_style ('/apps/admin/css/font-awesome/css/font-awesome-ie7.css');
}
$page->add_style ('/apps/admin/css/font-awesome/css/font-awesome.css');
$page->add_script ('/js/jquery-ui/jquery-ui.min.js');
$page->add_script ('/js/jquery.quickpager.js');
$page->add_style ('/apps/admin/js/redactor/redactor.css');
$page->add_script ('/apps/admin/js/redactor/redactor.min.js');
$page->add_script ('/apps/admin/js/redactor/plugins/dynamic.js');

$data['field_id'] = isset ($data['field_id']) ? $data['field_id'] : 'webpage-body';
$page->add_script ($tpl->render ('admin/wysiwyg', $data));

?>
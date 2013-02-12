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

$this->run ('admin/util/fontawesome');
$this->run ('admin/util/dynamicobjects');
$this->run ('filemanager/util/browser');
$this->run ('admin/util/redactor');

$page->add_style ('/js/jquery-ui/jquery-ui.css');

$page->add_script ('/js/jquery-ui/jquery-ui.min.js');
$page->add_script ('/js/jquery.quickpager.js');
$page->add_script ('/apps/admin/js/redactor/plugins/filebrowser.js');
$page->add_script ('/apps/admin/js/redactor/plugins/imagebrowser.js');
$page->add_script ('/apps/admin/js/redactor/plugins/dynamic.js');

$data['field_id'] = isset ($data['field_id']) ? $data['field_id'] : 'webpage-body';
$page->add_script ($tpl->render ('admin/wysiwyg', $data));

?>
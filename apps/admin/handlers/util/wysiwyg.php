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
 *
 * Alternately, you can disable the auto-initialization and call
 * it manually on an element like this:
 *
 *     {! admin/util/wysiwyg?field_id=0 !}
 *     
 *     <textarea id="edit-me"></textarea>
 *     
 *     <script>
 *     $(function () {
 *         $('#edit-me').wysiwyg ();
 *     });
 *     </script>
 */

$this->run ('admin/util/fontawesome');
$this->run ('admin/util/redactor');

$page->add_style ('/js/jquery-ui/jquery-ui.css');
$page->add_style ('/apps/admin/js/redactor/plugins/alignment.css');

$page->add_script ('/js/jquery-ui/jquery-ui.min.js');
$page->add_script ('/js/jquery.quickpager.js');
$page->add_script ('/apps/admin/js/redactor/plugins/alignment.js');
//$page->add_script ('/apps/admin/js/redactor/plugins/fullscreen.js');
$page->add_script ('/apps/admin/js/redactor/plugins/properties.js');
$page->add_script ('/apps/admin/js/redactor/plugins/source.js');
$page->add_script ('/apps/admin/js/redactor/plugins/table.js');
$page->add_script ('/apps/admin/js/redactor/plugins/superscript.js');
//$page->add_script ('/apps/admin/js/redactor/plugins/textdirection.js');
$page->add_script ('/apps/admin/js/redactor/plugins/undo.js');

$page->add_script (I18n::export (
	'Dynamic Objects',
	'Link',
	'Links',
	'Page',
	'Insert',
	'Insert Link',
	'Unlink',
	'Insert Image',
	'Insert File',
	'URL',
	'Email',
	'Text',
	'Open link in new tab',
	'Cancel',
	'- select -',
	'Files'
));

if (file_exists ('apps/admin/js/redactor/lang/' . $i18n->language . '_' . $i18n->locale . '.js')) {
	$page->add_script ('/apps/admin/js/redactor/lang/' . $i18n->language . '_' . $i18n->locale . '.js');
	$data['language'] = $i18n->language . '_' . $i18n->locale;
} elseif (file_exists ('apps/admin/js/redactor/lang/' . $i18n->language . '.js')) {
	$page->add_script ('/apps/admin/js/redactor/lang/' . $i18n->language . '.js');
	$data['language'] = $i18n->language;
} else {
	$data['language'] = 'en';
}

if (User::require_admin ()) {
	$this->run ('admin/util/dynamicobjects');
	$this->run ('filemanager/util/browser');
	$page->add_script ('/apps/admin/js/redactor/plugins/links.js');
	$page->add_script ('/apps/admin/js/redactor/plugins/imagebrowser.js');
	$page->add_script ('/apps/admin/js/redactor/plugins/filebrowser.js');
	$page->add_script ('/apps/admin/js/redactor/plugins/dynamic.js');

	$data['buttons'] = array (
		'format', 'bold', 'italic', 'deleted', 'superscript', 'alignment', 'horizontalrule',
		'lists', 'outdent', 'indent', 'undo'/*, 'links', 'imagebrowser',
		'filebrowser'*/, 'table', 'undo', 'source', 'dynamic'
	);
	
	$data['plugins'] = array (
		'superscript', 'alignment', 'properties', 'links', 'imagebrowser', 'filebrowser', 'table', 'undo', 'dynamic', 'source'
	);
} else {
	$data['buttons'] = array (
		'format', 'bold', 'italic', 'deleted', 'alignment', 'horizontalrule',
		'lists', 'outdent', 'indent', 'link',
		'table', 'undo', 'source'
	);
	
	$data['plugins'] = array (
		'superscript', 'alignment', 'properties', 'links', 'table', 'undo', 'source'
	);
}

$data['field_id'] = isset ($data['field_id'])
	? (($data['field_id'] === '0' || empty ($data['field_id'])) ? false : $data['field_id'])
	: 'webpage-body';

$page->add_script ($tpl->render ('admin/util/wysiwyg', $data));

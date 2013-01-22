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

$page->add_style ('/js/jquery-ui/jquery-ui.css');
$page->add_style ('/css/wysiwyg/jquery.wysiwyg.css');
$page->add_style ('/css/files/wysiwyg.fileManager.css');

$page->add_script ('/js/jquery-ui/jquery-ui.min.js');
$page->add_script ('/js/wysiwyg/jquery.wysiwyg.js');
$page->add_script ('/js/wysiwyg/controls/wysiwyg.image2.js');
$page->add_script ('/js/wysiwyg/controls/wysiwyg.link2.js');
$page->add_script ('/js/wysiwyg/controls/wysiwyg.table.js');
$page->add_script ('/js/wysiwyg/plugins/wysiwyg.fileManager.js');
$page->add_script ('/js/jquery.quickpager.js');
$page->add_script ('/js/wysiwyg/plugins/wysiwyg.embed.js');
$page->add_script ('/js/wysiwyg/plugins/wysiwyg.i18n.js');
if (file_exists ('js/wysiwyg/i18n/lang.' . $GLOBALS['i18n']->language . '.js')) {
	$page->add_script ('/js/wysiwyg/i18n/lang.' . $GLOBALS['i18n']->language . '.js');
}

$page->add_script ($tpl->render ('admin/wysiwyg', $data));

?>
<?php
 
/**
 * Fetches the properties manager for the file manager.
 */
 
$page->layout = 'admin';
 
$this->require_acl ('admin', 'user');
 
$res = PropManager::all();
$props = array();
 
foreach ($res as $k => $row) {
  $props[$k]['id'] = $row->id;
  $props[$k]['type'] = $row->type;
  $props[$k]['label'] = $row->label;
}
 
 
$page->title = __ ('Properties Manager');
$page->add_script ('/js/jquery-ui/jquery-ui.min.js');
$page->add_script ('/apps/filemanager/js/jquery.filemanager.js');
echo $tpl->render ('filemanager/propmanager', array('props' => $props, 'redirect' => $_GET['redirect']));
 
?> 
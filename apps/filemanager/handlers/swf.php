<?php

/**
 * Flash file embed handler. Used by the file manager in the WYSIWYG
 * editor when it recognizes an SWF file being embedded.
 */

$data['div'] = preg_replace ('/[^a-zA-Z0-9-]+/', '-', trim ($data['file'], '/'));

echo $tpl->render ('filemanager/swf', $data);

?>
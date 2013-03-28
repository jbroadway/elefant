<?php

/**
 * Call this to load the required libraries for the Redactor editor.
 * Note: For a full editor with the Elefant integrations, see
 * admin/util/wysiwyg instead.
 *
 * In PHP code, call it like this:
 *
 *     $this->run ('admin/util/redactor');
 *
 * In a view template, call it like this:
 *
 *     {! admin/util/redactor !}
 *
 * From here, you can initialize Redactor like this:
 *
 *     $('#my-textarea').redactor ({
 *         // editor options
 *     });
 */

$page->add_style ('/apps/admin/js/redactor/redactor.css');
$page->add_style ('/apps/admin/js/redactor/custom.css');

$page->add_script ('/apps/admin/js/redactor/redactor.min.js');

?>
<?php

/**
 * Provides a modal dialog to browse for files.
 *
 * Usage:
 *
 * 1. Load this handler either in your handler:
 *
 *     $this->run ('filemanager/util/browser');
 *
 * Or anywhere in your view:
 *
 *      {! filemanager/util/browser !}
 *
 * 2. Use the $.filebrowser() function to open the dialog window:
 *
 *      $.filebrowser ({
 *          allowed: ['jpg', 'jpeg', 'png', 'gif'],
 *          set_value: '#field-id',
 *          title: 'Choose an image',
 *          thumbs: true,
 *          callback: function (file) {
 *              console.log ('You chose: '  + file);
 *          }
 *     });
 *
 * Options:
 *
 * - allowed:   An array of allowed file extensions.
 * - callback:  A function to call with the chosen file link.
 * - set_value: The selector of an input field to update with the
 *   chosen file link.
 * - thumbs:    Whether to generate
 * - title:     A custom title for the dialog window.
 */

$this->run ('admin/util/modal');

$page->add_style ('/apps/filemanager/css/filebrowser.css');
$page->add_script ('/apps/filemanager/js/filemanager.js');
$page->add_script ('/apps/filemanager/js/jquery.filebrowser.js');

?>
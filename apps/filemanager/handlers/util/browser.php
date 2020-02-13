<?php

/**
 * Provides a modal dialog to browse for files.
 *
 * Usage:
 *
 * ### 1. Load this handler either in your handler:
 *
 *     $this->run ('filemanager/util/browser');
 *
 * Or anywhere in your view:
 *
 *      {! filemanager/util/browser !}
 *
 * ### 2. Use the $.filebrowser() function to open the dialog window:
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
 * - `allowed`   - An array of allowed file extensions.
 * - `callback`  - A function to call with the chosen file link.
 * - `set_value` - The selector of an input field to update with the
 *   chosen file link.
 * - `thumbs`    - Whether to show thumbnails instead of file names.
 *   Note: also automatically sets allowed list to jpeg, png, and
 *   gif formats so you don't have to set allowed explicitly.
 * - `title`     - A custom title for the dialog window.
 */

echo $this->run ('admin/util/modal');

$f = new Form ('post', $this);
$f->initialize_csrf ();

$page->add_style ('/apps/filemanager/css/filebrowser.css');
$page->add_script (
	sprintf (
		'<script>var filemanager_path = "%s", filemanager_upload = %d, filemanager_token = "%s";</script>',
		conf('Paths','filemanager_path'),
		(int) User::require_acl ('admin', 'filemanager'),
		$f->csrf_token
	)
);
$page->add_script ('/apps/filemanager/js/jquery.filedrop.js');
$page->add_script ('/apps/filemanager/js/filemanager.js?v=2');
$page->add_script ('/apps/filemanager/js/jquery.filebrowser.js?v=3');
$page->add_script (
	sprintf (
		'<script>var filebrowser_max_filesize = %s;</script>',
		(int) ini_get ('upload_max_filesize')
	)
);
$page->add_script (
	I18n::export (
		array (
			'Choose a file',
			'New file',
			'Please upload one of the following file types',
			'Your browser does not support drag and drop file uploads.',
			'Please upload fewer files at a time.',
			'The following file is too large to upload',
			'Uploading...',
			'Select'
		)
	)
);

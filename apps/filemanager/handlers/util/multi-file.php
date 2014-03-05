<?php

/**
 * Provides a multi-file selector based on the file browser
 * from filemanager/util/browser.
 *
 * Usage:
 *
 * 1. Load this handler either in your handler:
 *
 *     $this->run ('filemanager/util/multi-file');
 *
 * Or anywhere in your view:
 *
 *     {! filemanager/util/multi-file !}
 *
 * 2. Create the HTML elements for the input field and the
 * preview area:
 *
 *     <p>
 *         {"Attach files"}:
 *         <div id="preview"></div>
 *         <input type="hidden" name="files" id="files" />
 *     </p>
 *
 * 3. Use the $.multi_file() function to initialize the plugin:
 *
 *     $.multi_image ({
 *         field: '#files',
 *         preview: '#preview'
 *     });
 *
 * This helper stores the list of files in the following format
 * in the input field, so you can easily split them into an array:
 *
 *     /files/file1.txt|/files/file2.doc|/files/file3.txt
 *
 * Options:
 *
 * - field:   The selector of an input field to update with the list.
 * - preview: The selector of an element to use to contain the list preview.
 */

echo $this->run ('filemanager/util/browser');
echo $this->run ('admin/util/fontawesome');

$page->add_style ('/apps/filemanager/css/multi-file.css');
$page->add_script (
	sprintf (
		'<script>var filemanager_path = "%s";</script>',
		conf('Paths','filemanager_path')
	)
);
$page->add_script ('/js/jquery-ui/jquery-ui.min.js');
$page->add_script ('/apps/filemanager/js/jquery.multi-file.js');
$page->add_script (I18n::export (
	'Click to remove',
	'Browse files'
));

?>